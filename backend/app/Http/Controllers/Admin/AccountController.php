<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    use AuditsActions;

    public function index()
    {
        return view('admin.accounts.index', [
            'users' => User::with(['teacher', 'student', 'parentProfile'])->orderBy('created_at', 'desc')->get(),
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

        $this->audit($request, 'account.created', User::class, $user->id, null, [
            'type' => $data['type'],
            'temp_password' => $temporaryPassword,
        ]);

        return redirect()->route('admin.accounts.create')
            ->with('status', "Account created successfully. Temporary password: {$temporaryPassword}");
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
