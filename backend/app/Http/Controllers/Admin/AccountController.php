<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Mail\EnrollmentNotification;
use App\Models\ImportCredential;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Traits\AuditsActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class AccountController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $role = $request->query('role');
        $validRoles = ['teacher', 'student', 'parent', 'admin', 'bursar'];

        $users = User::with(['teacher', 'student', 'parentProfile'])
            ->when(in_array($role, $validRoles), fn ($q) => $q->where('role', $role))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.accounts.index', [
            'users' => $users,
            'currentRole' => $role,
            'roles' => $validRoles,
        ]);
    }

    public function create()
    {
        return view('admin.accounts.create', [
            'teachers' => Teacher::with('user')->get(),
            'students' => Student::with(['user', 'schoolClass'])->get(),
            'parents' => ParentProfile::with('user')->get(),
            'classes' => SchoolClass::with('formTeacher')->get(),
        ]);
    }

    public function store(StoreAccountRequest $request)
    {
        $data = $request->validated();
        $temporaryPassword = $data['password'] ?? Str::random(20);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'staff_id' => $data['staff_id'] ?? null,
            'password' => Hash::make($temporaryPassword),
            'phone' => $data['phone'] ?? null,
            'role' => $data['type'],
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        $account = match ($data['type']) {
            'teacher' => $this->createTeacherAccount($user, $request),
            'student' => $this->createStudentAccount($user, $request),
            'parent' => $this->createParentAccount($user, $request),
            'bursar' => $this->createBursarAccount($user, $request),
        };

        $loginIdentifier = $user->loginIdentifier();
        $this->persistAccountCredential($user, $data['type'], $loginIdentifier);

        $oneTimeCredentials = [[
            'role' => $data['type'],
            'name' => $user->name,
            'email' => $user->email,
            'login_identifier' => $loginIdentifier,
            'password' => in_array($data['type'], ['student', 'teacher', 'bursar'], true) ? $temporaryPassword : null,
        ]];

        if (in_array($data['type'], ['student', 'teacher', 'bursar'], true)) {
            ImportCredential::storeTemporaryPassword($user, $temporaryPassword, (int) auth()->id());
        }

        $parentProfile = $account instanceof Student ? $account->parents()->with('user')->first() : null;
        if ($parentProfile && $parentProfile->user->getAttribute('initial_password')) {
            $this->persistAccountCredential($parentProfile->user, 'parent', $parentProfile->user->loginIdentifier());
            $oneTimeCredentials[] = [
                'role' => 'parent',
                'name' => $parentProfile->user->name,
                'email' => $parentProfile->user->email,
                'login_identifier' => $parentProfile->user->loginIdentifier(),
                'password' => null,
            ];
        }

        try {
            if ($user->email) {
                Mail::to($user->email)->send(new EnrollmentNotification($user));
            }
        } catch (\Exception $e) {
        }

        $this->audit($request, 'account.created', User::class, $user->id, null, [
            'type' => $data['type'],
        ]);

        return redirect()->route('admin.accounts.create')
            ->with('status', 'Account created successfully.')
            ->with('new_account_credentials', $oneTimeCredentials);
    }

    public function credentials(Request $request)
    {
        $filters = $request->validate([
            'role' => ['nullable', Rule::in(['student', 'teacher', 'parent', 'admin', 'bursar'])],
            'search' => ['nullable', 'string', 'max:100'],
            'password_status' => ['nullable', Rule::in(['required', 'changed'])],
        ]);
        $filters = [
            'role' => $filters['role'] ?? '',
            'search' => trim($filters['search'] ?? ''),
            'password_status' => $filters['password_status'] ?? '',
        ];
        $users = $this->credentialsQuery($filters)
            ->with(['teacher', 'student', 'parentProfile'])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();
        $eligibleUsers = $users->getCollection()
            ->filter(function (User $user): bool {
                return $user->needs_password_change
                    && match ($user->role) {
                        'student' => $user->student !== null,
                        'teacher' => $user->teacher !== null,
                        'bursar' => true,
                        default => false,
                    };
            });
        $eligibleUsersById = $eligibleUsers->keyBy('id');
        $temporaryPasswords = ImportCredential::query()
            ->whereIn('user_id', $eligibleUsersById->keys())
            ->whereIn('role', ['student', 'teacher', 'bursar'])
            ->whereNotNull('temporary_password')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->filter(function (ImportCredential $credential) use ($eligibleUsersById): bool {
                $user = $eligibleUsersById->get($credential->user_id);

                return $user !== null
                    && $credential->role === $user->role
                    && filled($credential->temporary_password)
                    && Hash::check($credential->temporary_password, $user->password);
            })
            ->mapWithKeys(fn (ImportCredential $credential): array => [$credential->user_id => $credential->temporary_password]);

        return view('admin.accounts.credentials', [
            'credentials' => $users,
            'currentRole' => $filters['role'],
            'search' => $filters['search'],
            'passwordStatus' => $filters['password_status'],
            'roles' => ['student', 'teacher', 'parent', 'admin', 'bursar'],
            'roleLabels' => [
                'admin' => 'Admin',
                'bursar' => 'Bursar / Financial Officer',
                'teacher' => 'Teacher',
                'student' => 'Student',
                'parent' => 'Parent/Guardian',
            ],
            'temporaryPasswords' => $temporaryPasswords,
        ]);
    }

    public function downloadAllCredentials(Request $request)
    {
        $filters = $request->validate([
            'role' => ['nullable', Rule::in(['student', 'teacher', 'parent', 'admin', 'bursar'])],
            'search' => ['nullable', 'string', 'max:100'],
            'password_status' => ['nullable', Rule::in(['required', 'changed'])],
        ]);

        $filters['search'] = trim($filters['search'] ?? '');
        $query = $this->credentialsQuery($filters);

        return Response::stream(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Role', 'Name', 'Login ID', 'Email', 'Password Status']);

            $query->with(['teacher', 'student', 'parentProfile'])
                ->chunkById(250, function ($users) use ($output): void {
                    foreach ($users as $user) {
                        fputcsv($output, [
                            ucfirst($user->role),
                            $user->name,
                            $user->loginIdentifier() ?? '',
                            $user->email ?? '',
                            $user->needs_password_change ? 'Password Change Required' : 'Password Changed',
                        ]);
                    }
                });

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="all-credentials.csv"',
        ]);
    }

    public function destroy(User $user)
    {
        // Prevent deactivation of the currently authenticated admin.
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'You cannot deactivate your own account.']);
        }

        // Prevent deactivation of admin accounts.
        if ($user->role === 'admin') {
            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'Admin accounts cannot be deactivated.']);
        }

        try {
            $user->update(['is_active' => false]);

            return redirect()->route('admin.accounts.index')
                ->with('status', 'Account "' . e($user->name) . '" deactivated successfully.');
        } catch (\Exception $e) {
            Log::error('Account deactivation failed.', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'exception' => $e,
            ]);

            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'Failed to deactivate account. Please try again or contact the system administrator.']);
        }
    }

    public function permanentDelete(Request $request, User $user)
    {
        // Prevent self-deletion.
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'You cannot delete your own account.']);
        }

        // Prevent deletion of administrator accounts.
        if ($user->role === 'admin') {
            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'Administrator accounts cannot be permanently deleted.']);
        }

        // Protect the last active administrator.
        $adminCount = User::where('role', 'admin')->where('is_active', true)->count();
        if ($adminCount <= 1 && User::where('id', $user->id)->where('role', 'admin')->where('is_active', true)->exists()) {
            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'Cannot delete the last active administrator.']);
        }

        // Only bursar accounts may be permanently deleted (no dependent profile table).
        if ($user->role !== 'bursar') {
            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'Only bursar accounts are eligible for permanent deletion. Other roles must be deactivated instead.']);
        }

        // Only inactive bursar accounts may be permanently deleted.
        if ($user->is_active) {
            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'Only inactive bursar accounts are eligible for permanent deletion. Deactivate the account first.']);
        }

        // Inspect every dependent record before allowing deletion.
        $dependentChecks = [
            'teacher profile' => $user->teacher()->exists(),
            'student profile' => $user->student()->exists(),
            'parent profile' => $user->parentProfile()->exists(),
            'attendance records' => \App\Models\Attendance::where('marked_by', $user->id)->exists(),
            'announcements' => \App\Models\Announcement::where('created_by', $user->id)->exists(),
            'payment records' => \App\Models\Payment::where('recorded_by', $user->id)->exists(),
            'payment reversals' => \App\Models\PaymentReversal::where('reversed_by', $user->id)->exists(),
            'fee packages' => \App\Models\FeePackage::where('created_by', $user->id)->exists(),
            'fee package assignments' => \App\Models\FeePackageAssignment::where('assigned_by', $user->id)->exists(),
            'import credentials created' => \App\Models\ImportCredential::where('created_by', $user->id)->exists(),
        ];

        foreach ($dependentChecks as $label => $exists) {
            if ($exists) {
                return redirect()->route('admin.accounts.index')
                    ->withErrors(['account.delete' => 'Cannot permanently delete "' . e($user->name) . '": ' . ucfirst($label) . ' exist and must be preserved. Deactivate the account instead.']);
            }
        }

        try {
            DB::transaction(function () use ($request, $user): void {
                $this->audit($request, 'account.permanently_deleted', User::class, $user->id, null, [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]);

                $user->delete();
            });

            return redirect()->route('admin.accounts.index')
                ->with('status', 'Account "' . e($user->name) . '" permanently deleted.');
        } catch (\Exception $e) {
            Log::error('Account permanent deletion failed.', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'exception' => $e,
            ]);

            return redirect()->route('admin.accounts.index')
                ->withErrors(['account.delete' => 'Failed to permanently delete account. Please try again or contact the system administrator.']);
        }
    }

    protected function persistAccountCredential(User $user, string $role, ?string $loginIdentifier): void
    {
        ImportCredential::create([
            'role' => $role,
            'name' => $user->name,
            'email' => $user->email,
            'login_identifier' => $loginIdentifier,
            'password' => null,
            'related_to' => null,
            'user_id' => $user->id,
            'created_by' => auth()->id(),
            'expires_at' => null,
        ]);
    }

    public function regenerateCredentialPassword(Request $request, User $user)
    {
        if (! $this->supportsTemporaryCredential($user)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Temporary passwords can only be generated for valid student, teacher, or bursar accounts.'], 404);
            }

            abort(404);
        }

        $temporaryPassword = Str::password(20);
        DB::transaction(function () use ($request, $user, $temporaryPassword): void {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless($this->supportsTemporaryCredential($user), 404);

            $user->update([
                'password' => Hash::make($temporaryPassword),
                'needs_password_change' => true,
            ]);
            ImportCredential::storeTemporaryPassword($user, $temporaryPassword, (int) $request->user()->id);

            $this->audit($request, 'account.temporary_password_regenerated', User::class, $user->id, null, [
                'role' => $user->role,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Temporary password generated successfully.',
                'password' => $temporaryPassword,
                'password_status' => 'Password Change Required',
            ])->header('Cache-Control', 'no-store, private');
        }

        return redirect()->route('admin.accounts.credentials')
            ->with('status', 'Temporary password generated successfully.');
    }

    private function supportsTemporaryCredential(User $user): bool
    {
        return match ($user->role) {
            'student' => $user->student()->exists(),
            'teacher' => $user->teacher()->exists(),
            'bursar' => true, // Bursar accounts always use email login
            default => false,
        };
    }

    private function credentialsQuery(array $filters): Builder
    {
        $query = User::query();

        if ($filters['role'] ?? false) {
            $query->where('role', $filters['role']);
        }

        if (($filters['password_status'] ?? '') === 'required') {
            $query->where('needs_password_change', true);
        } elseif (($filters['password_status'] ?? '') === 'changed') {
            $query->where('needs_password_change', false);
        }

        $search = mb_strtolower(trim($filters['search'] ?? ''));
        if ($search !== '') {
            $pattern = '%'.$search.'%';
            preg_match('/^parent-(\d+)$/', $search, $parentIdentifier);

            $query->where(function (Builder $accounts) use ($search, $pattern, $parentIdentifier): void {
                $accounts->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$pattern])
                    ->orWhereHas('student', fn (Builder $student) => $student->whereRaw('LOWER(admission_no) LIKE ?', [$pattern]))
                    ->orWhereHas('teacher', fn (Builder $teacher) => $teacher->whereRaw('LOWER(employee_id) LIKE ?', [$pattern]));

                if ($parentIdentifier) {
                    $accounts->orWhereHas('parentProfile', fn (Builder $parent) => $parent->whereKey((int) $parentIdentifier[1]));
                }
            });
        }

        return $query;
    }

    protected function createTeacherAccount(User $user, Request $request): Teacher
    {
        return Teacher::create([
            'user_id' => $user->id,
            'qualification' => $request->input('qualification'),
            'phone' => $request->input('phone'),
            'employee_id' => $request->staff_id,
        ]);
    }

    protected function createStudentAccount(User $user, Request $request): Student
    {
        $student = Student::create([
            'user_id' => $user->id,
            'admission_no' => $request->admission_no,
            'class_id' => $request->input('class_id'),
            'gender' => $request->input('gender'),
            'date_of_birth' => $request->input('date_of_birth'),
            'status' => 'active',
        ]);

        if ($request->input('parent_email')) {
            $parent = $this->getOrCreateParent($request->input('parent_email'), $user->name);
            $student->parents()->attach($parent->id);
            $user->update(['phone' => $parent->user->phone]);
            if ($parent->getAttribute('initial_password')) {
                $student->setAttribute('parent_initial_password', $parent->getAttribute('initial_password'));
            }
        }

        return $student;
    }

    protected function generateEmployeeId(): string
    {
        do {
            $employeeId = 'T-'.Str::upper(Str::random(6));
        } while (Student::whereRaw('LOWER(admission_no) = ?', [mb_strtolower($employeeId)])->exists()
            || User::whereIn('role', ['admin', 'teacher', 'parent'])
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($employeeId)])->exists()
            || Teacher::whereRaw('LOWER(employee_id) = ?', [mb_strtolower($employeeId)])->exists());

        return $employeeId;
    }

    protected function generateAdmissionNo(): string
    {
        do {
            $admissionNo = 'ADM-'.Str::upper(Str::random(6));
        } while (Student::whereRaw('LOWER(admission_no) = ?', [mb_strtolower($admissionNo)])->exists()
            || Teacher::whereRaw('LOWER(employee_id) = ?', [mb_strtolower($admissionNo)])->exists()
            || User::whereIn('role', ['admin', 'teacher', 'parent'])
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($admissionNo)])->exists());

        return $admissionNo;
    }

    protected function getOrCreateParent(string $parentEmail, string $studentName): ParentProfile
    {
        $existingParent = ParentProfile::whereHas('user', fn ($q) => $q->where('email', $parentEmail))->first();
        if ($existingParent) {
            return $existingParent;
        }

        $temporaryPassword = Str::random(20);
        $parentUser = User::create([
            'name' => 'Parent of '.$studentName,
            'email' => $parentEmail,
            'password' => Hash::make($temporaryPassword),
            'role' => 'parent',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'occupation' => null,
            'phone' => null,
        ]);
        $parent->setAttribute('initial_password', $temporaryPassword);

        return $parent;
    }

    protected function createParentAccount(User $user, Request $request): ParentProfile
    {
        return ParentProfile::create([
            'user_id' => $user->id,
            'occupation' => $request->input('occupation'),
            'phone' => $request->input('phone'),
        ]);
    }

    protected function createBursarAccount(User $user, Request $request): User
    {
        // Bursar accounts don't need a separate profile table - they use the User model directly
        // The User model already has all necessary fields (name, email, phone, etc.)
        // Staff ID is already saved on the User record during account creation.
        $user->save();
        return $user;
    }

    public function parentCommunication()
    {
        return view('teacher.parents.communicate');
    }

    public function assessments()
    {
        return view('teacher.assessments');
    }

    public function teacherProfile()
    {
        return view('teacher.profile');
    }
}



