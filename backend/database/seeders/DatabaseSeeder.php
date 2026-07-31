<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Parent User',
                'email' => 'parent@example.com',
                'password' => Hash::make('password123'),
                'role' => 'parent',
            ],
            [
                'name' => 'Student User',
                'email' => 'student@example.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ],
        ];

        $createdUsers = [];

        foreach ($users as $user) {
            $createdUsers[] = User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                ]
            );
        }

        $teacherUser = $createdUsers[1];
        $studentUser = $createdUsers[3];

        $teacher = Teacher::updateOrCreate(
            ['employee_id' => 'T-ADMIN01'],
            [
                'user_id' => $teacherUser->id,
                'qualification' => 'B.Ed',
            ]
        );

        $class = SchoolClass::updateOrCreate(
            ['name' => 'JSS 1'],
            ['form_teacher_id' => $teacher->id]
        );

        $session = AcademicSession::updateOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2027-07-31',
                'is_current' => true,
            ]
        );

        $term = Term::updateOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'First Term'],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2026-12-18',
                'is_current' => true,
            ]
        );

        $subject = Subject::firstOrCreate(['name' => 'Mathematics']);

        $classSubject = ClassSubject::updateOrCreate(
            ['class_id' => $class->id, 'subject_id' => $subject->id],
            ['is_compulsory' => true]
        );

        TeacherClassSubject::updateOrCreate(
            ['teacher_id' => $teacher->id, 'class_subject_id' => $classSubject->id],
            ['is_active' => true]
        );

        Student::updateOrCreate(
            ['user_id' => $studentUser->id],
            [
                'admission_no' => 'ADM-001',
                'class_id' => $class->id,
                'status' => 'active',
            ]
        );
    }
}
