<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@demo.school'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('AdminTest123!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $session = AcademicSession::updateOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2027-07-31',
                'is_current' => true,
            ]
        );

        $term1 = Term::updateOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'First Term'],
            ['start_date' => '2026-09-01', 'end_date' => '2026-12-18', 'is_current' => true]
        );

        $term2 = Term::updateOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'Second Term'],
            ['start_date' => '2027-01-15', 'end_date' => '2027-04-09', 'is_current' => false]
        );

        $term3 = Term::updateOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'Third Term'],
            ['start_date' => '2027-04-26', 'end_date' => '2027-07-31', 'is_current' => false]
        );

        $classJss1 = SchoolClass::updateOrCreate(['name' => 'JSS 1']);
        $classJss2 = SchoolClass::updateOrCreate(['name' => 'JSS 2']);
        $classSss1 = SchoolClass::updateOrCreate(['name' => 'SSS 1']);
        $classSss2 = SchoolClass::updateOrCreate(['name' => 'SSS 2']);

        $subjects = [];
        $subjectNames = [
            'English Language',
            'Mathematics',
            'Basic Science',
            'Social Studies',
            'Computer Studies',
            'Biology',
            'Chemistry',
            'Physics',
        ];

        foreach ($subjectNames as $name) {
            $subjects[$name] = Subject::firstOrCreate(['name' => $name]);
        }

        $classSubjectMap = [
            'English Language' => [$classJss1, $classJss2, $classSss1, $classSss2],
            'Mathematics' => [$classJss1, $classJss2, $classSss1, $classSss2],
            'Basic Science' => [$classJss1, $classJss2],
            'Social Studies' => [$classJss1, $classJss2],
            'Computer Studies' => [$classJss1, $classJss2, $classSss1, $classSss2],
            'Biology' => [$classSss1, $classSss2],
            'Chemistry' => [$classSss1, $classSss2],
            'Physics' => [$classSss1, $classSss2],
        ];

        $periodsPerWeek = [
            'English Language' => 4,
            'Mathematics' => 4,
            'Basic Science' => 3,
            'Social Studies' => 3,
            'Computer Studies' => 2,
            'Biology' => 3,
            'Chemistry' => 3,
            'Physics' => 3,
        ];

        $classSubjects = [];

        foreach ($classSubjectMap as $subjectName => $classes) {
            foreach ($classes as $class) {
                $key = "{$subjectName}-{$class->name}";
                $classSubjects[$key] = ClassSubject::updateOrCreate(
                    ['class_id' => $class->id, 'subject_id' => $subjects[$subjectName]->id],
                    ['is_compulsory' => true, 'periods_per_week' => $periodsPerWeek[$subjectName] ?? 1]
                );
            }
        }

        $teacherData = [
            ['Sarah Adeyemi', 'sarah.adeyemi@demo.school', 'T-1001', 'B.Ed Mathematics'],
            ['Daniel Okafor', 'daniel.okafor@demo.school', 'T-1002', 'B.Sc Computer Science'],
            ['Grace Williams', 'grace.williams@demo.school', 'T-1003', 'B.Ed English'],
            ['Samuel Ibrahim', 'samuel.ibrahim@demo.school', 'T-1004', 'B.Sc Chemistry'],
            ['Linda Eze', 'linda.eze@demo.school', 'T-1005', 'B.Sc Biology'],
            ['Michael Yusuf', 'michael.yusuf@demo.school', 'T-1006', 'B.Sc Physics'],
        ];

        $teachers = [];

        foreach ($teacherData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data[1]],
                [
                    'name' => $data[0],
                    'password' => Hash::make('SchoolTest123!'),
                    'role' => 'teacher',
                    'is_active' => true,
                ]
            );

            $teachers[$data[0]] = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => $data[2],
                    'qualification' => $data[3],
                ]
            );
        }

        $formTeacherMap = [
            [$classJss1, $teachers['Sarah Adeyemi']],
            [$classJss2, $teachers['Daniel Okafor']],
            [$classSss1, $teachers['Grace Williams']],
            [$classSss2, $teachers['Samuel Ibrahim']],
        ];

        foreach ($formTeacherMap as [$class, $teacher]) {
            $class->update(['form_teacher_id' => $teacher->id]);

            ClassAssignment::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'class_id' => $class->id,
                    'academic_session_id' => $session->id,
                    'term_id' => $term1->id,
                ]
            );
        }

        $teacherSubjectMap = [
            'Sarah Adeyemi' => [[$classJss1, 'Mathematics'], [$classJss2, 'Mathematics'], [$classSss1, 'Mathematics'], [$classSss2, 'Mathematics'], [$classJss1, 'Basic Science']],
            'Daniel Okafor' => [[$classJss1, 'Computer Studies'], [$classJss2, 'Computer Studies'], [$classSss1, 'Computer Studies'], [$classSss2, 'Computer Studies']],
            'Grace Williams' => [[$classJss1, 'English Language'], [$classJss2, 'English Language'], [$classSss1, 'English Language'], [$classSss2, 'English Language']],
            'Samuel Ibrahim' => [[$classSss1, 'Chemistry'], [$classSss2, 'Chemistry']],
            'Linda Eze' => [[$classSss1, 'Biology'], [$classSss2, 'Biology']],
            'Michael Yusuf' => [[$classSss1, 'Physics'], [$classSss2, 'Physics']],
        ];

        foreach ($teacherSubjectMap as $teacherName => $assignments) {
            foreach ($assignments as [$class, $subjectName]) {
                $csKey = "{$subjectName}-{$class->name}";
                TeacherClassSubject::updateOrCreate(
                    [
                        'teacher_id' => $teachers[$teacherName]->id,
                        'class_subject_id' => $classSubjects[$csKey]->id,
                    ],
                    ['is_active' => true, 'assigned_at' => now()]
                );
            }
        }
    }
}
