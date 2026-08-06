<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CsvExportService;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolAdminController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected CsvExportService $csvExportService,
    ) {}

    public function index()
    {
        return view('admin.index', [
            'classes' => SchoolClass::count(),
            'teachers' => Teacher::count(),
            'students' => Student::count(),
            'parents' => ParentProfile::count(),
        ]);
    }

    public function classes()
    {
        return view('admin.classes.index', [
            'classes' => SchoolClass::with('formTeacher.user')->get(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function teachers()
    {
        return view('admin.teachers.index', [
            'teachers' => Teacher::with('user')->get(),
            'users' => User::where('role', 'teacher')->where('needs_password_change', true)->get(),
        ]);
    }

    public function students()
    {
        return view('admin.students.index', [
            'students' => Student::with(['user', 'class'])->get(),
            'classes' => SchoolClass::all(),
            'users' => User::where('role', 'student')->where('needs_password_change', true)->get(),
        ]);
    }

    public function createClass(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'form_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        if (empty($data['name'])) {
            return back()->withErrors(['name' => 'Class name is required.']);
        }

        SchoolClass::create($data);

        $this->audit($request, 'class.created', SchoolClass::class, SchoolClass::query()->latest('id')->value('id'), null, $data);

        return redirect()->route('admin.classes')->with('status', 'Class created.');
    }

    public function createTeacher(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $temporaryPassword = $data['password'] ?? Str::random(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($temporaryPassword),
            'phone' => $data['phone'] ?? null,
            'role' => 'teacher',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'qualification' => $data['qualification'],
            'phone' => $data['phone'] ?? null,
            'employee_id' => 'T-'.Str::upper(Str::random(6)),
        ]);

        $this->audit($request, 'teacher.created', Teacher::class, Teacher::query()->latest('id')->value('id'), null, [
            'user_id' => $user->id,
            'temp_password' => $temporaryPassword,
        ]);

        return redirect()->route('admin.teachers')->with('status', "Teacher created. Temporary password: {$temporaryPassword}");
    }

    public function createStudent(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'parent_email' => ['nullable', 'email'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $temporaryPassword = $data['password'] ?? Str::random(12);
        $admissionNumber = 'ADM-'.Str::upper(Str::random(6));

        $studentData = [
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'admission_no' => $admissionNumber,
            'status' => 'active',
        ];

        $parent = null;

        if (! empty($data['parent_email'])) {
            $parent = $this->getOrCreateParent($data['parent_email'], $data);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($temporaryPassword),
            'role' => 'student',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        if (empty($data['class_id'])) {
            $data['class_id'] = SchoolClass::query()->value('id') ?? SchoolClass::create(['name' => 'Default Class'])->id;
        }

        $student = Student::create(array_merge($studentData, [
            'user_id' => $user->id,
            'class_id' => $data['class_id'],
        ]));

        if ($parent) {
            $student->parents()->attach($parent->id);
        }

        $this->audit($request, 'student.created', Student::class, $student->id, null, [
            'user_id' => $user->id,
            'admission_no' => $admissionNumber,
            'temp_password' => $temporaryPassword,
        ]);

        $redirectMessage = "Student created. Admission No: {$admissionNumber}";
        if ($parent) {
            $redirectMessage .= ', Parent linked';
        }
        $redirectMessage .= ". Temporary password: {$temporaryPassword}";

        return redirect()->route('admin.students')->with('status', $redirectMessage);
    }

    protected function getOrCreateParent(string $parentEmail, array $requestData)
    {
        $existingParent = ParentProfile::whereHas('user', fn ($q) => $q->where('email', $parentEmail))->first();

        if ($existingParent) {
            return $existingParent;
        }

        $parentUser = User::create([
            'name' => $requestData['parent_name'] ?? 'Parent of '.$requestData['name'],
            'email' => $parentEmail,
            'password' => Hash::make(Str::random(12)),
            'phone' => $requestData['parent_phone'] ?? null,
            'role' => 'parent',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        return ParentProfile::create([
            'user_id' => $parentUser->id,
            'occupation' => $requestData['parent_occupation'] ?? null,
            'phone' => $requestData['parent_phone'] ?? null,
        ]);
    }

    public function exportStudents(Request $request)
    {
        return $this->csvExportService->exportStudents($request);
    }
}
