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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $role = $request->query('role');

        $validRoles = ['teacher', 'student', 'parent', 'admin'];

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

        $temporaryPassword = $data['password'] ?? Str::random(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($temporaryPassword),
            'phone' => $data['phone'] ?? null,
            'role' => $data['type'],
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        $accountId = null;

        match ($data['type']) {
            'teacher' => $accountId = $this->createTeacherAccount($user, $request),
            'student' => $accountId = $this->createStudentAccount($user, $request),
            'parent' => $accountId = $this->createParentAccount($user, $request),
        };

        $this->persistAccountCredential($user, $data['type'], $temporaryPassword);

        try {
            $relatedStudent = null;
            if ($data['type'] === 'student' && $accountId && $accountId->user && $accountId->user->parentProfile) {
                $relatedStudent = $accountId->user->parentProfile->user->name ?? null;
            } elseif ($data['type'] === 'parent') {
                $relatedStudent = $data['name'];
            }

            Mail::to($user->email)->send(new EnrollmentNotification(
                $user,
                $temporaryPassword,
                $relatedStudent ?? '',
            ));
        } catch (\Exception $e) {
        }

        $this->audit($request, 'account.created', User::class, $user->id, null, [
            'type' => $data['type'],
            'temp_password' => $temporaryPassword,
        ]);

        return redirect()->route('admin.accounts.create')
            ->with('status', "Account created successfully. Temporary password: {$temporaryPassword}")
            ->with('new_account_credentials', [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['type'],
                'password' => $temporaryPassword,
            ]);
    }

    public function credentials(Request $request)
    {
        $role = $request->query('role');
        $validRoles = ['teacher', 'student', 'parent', 'admin'];

        $importCredentials = ImportCredential::with('user')
            ->when(in_array($role, $validRoles), fn ($q) => $q->where('role', $role))
            ->orderBy('created_at', 'desc')
            ->get();

        $seededUsers = User::when(in_array($role, $validRoles), fn ($q) => $q->where('role', $role))
            ->whereDoesntHave('importCredential')
            ->orderBy('created_at', 'desc')
            ->get();

        $credentials = $importCredentials->merge($seededUsers);

        $roleLabels = [
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
        ];

        return view('admin.accounts.credentials', [
            'credentials' => $credentials,
            'currentRole' => $role,
            'roles' => $validRoles,
            'roleLabels' => $roleLabels,
        ]);
    }

    public function downloadAllCredentials(Request $request)
    {
        $role = $request->query('role');
        $validRoles = ['teacher', 'student', 'parent', 'admin'];

        $credentials = ImportCredential::with('user')
            ->when(in_array($role, $validRoles), fn ($q) => $q->where('role', $role))
            ->orderBy('created_at', 'desc')
            ->get();

        $response = Response::stream(function () use ($credentials) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Role', 'Name', 'Email', 'Temporary Password', 'Related Student', 'Status']);

            foreach ($credentials as $credential) {
                $pwd = $credential->password;
                if ($credential->user && ! $credential->user->needs_password_change) {
                    $pwd = '[CHANGED]';
                }
                fputcsv($output, [
                    ucfirst($credential->role),
                    $credential->name,
                    $credential->email,
                    $pwd,
                    $credential->related_to ?? '',
                    $credential->user && ! $credential->user->needs_password_change
                        ? 'Password Changed'
                        : ($credential->user ? 'Needs Change' : 'N/A'),
                ]);
            }

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="all-credentials.csv"',
        ]);

        return $response;
    }

    protected function persistAccountCredential(User $user, string $role, string $password): void
    {
        ImportCredential::create([
            'role' => $role,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'related_to' => null,
            'user_id' => $user->id,
            'created_by' => auth()->id(),
            'expires_at' => null,
        ]);
    }

    protected function createTeacherAccount(User $user, Request $request)
    {
        return Teacher::create([
            'user_id' => $user->id,
            'qualification' => $request->input('qualification'),
            'phone' => $request->input('phone'),
            'employee_id' => 'T-'.Str::upper(Str::random(6)),
        ]);
    }

    protected function createStudentAccount(User $user, Request $request)
    {
        $classId = $request->input('class_id');
        $gender = $request->input('gender');
        $dateOfBirth = $request->input('date_of_birth');

        $student = Student::create([
            'user_id' => $user->id,
            'admission_no' => 'ADM-'.Str::upper(Str::random(6)),
            'class_id' => $classId,
            'gender' => $gender,
            'date_of_birth' => $dateOfBirth,
            'status' => 'active',
        ]);

        if ($request->input('parent_email')) {
            $parent = $this->getOrCreateParent($request->input('parent_email'), $user->name);
            if ($parent) {
                $student->parents()->attach($parent->id);
                $user->update(['phone' => $parent->user->phone]);
            }
        }

        return $student;
    }

    protected function getOrCreateParent(string $parentEmail, string $studentName)
    {
        $existingParent = ParentProfile::whereHas('user', fn ($q) => $q->where('email', $parentEmail))->first();

        if ($existingParent) {
            return $existingParent;
        }

        $parentUser = User::create([
            'name' => 'Parent of '.$studentName,
            'email' => $parentEmail,
            'password' => Hash::make(Str::random(12)),
            'role' => 'parent',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        return ParentProfile::create([
            'user_id' => $parentUser->id,
            'occupation' => null,
            'phone' => null,
        ]);
    }

    protected function createParentAccount(User $user, Request $request)
    {
        $parent = ParentProfile::create([
            'user_id' => $user->id,
            'occupation' => $request->input('occupation'),
            'phone' => $request->input('phone'),
        ]);

        return $parent;
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
