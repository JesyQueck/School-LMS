<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\FeeType;
use App\Models\ParentProfile;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
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
            ['name' => 'Admin User', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        $teacherUser1 = User::updateOrCreate(
            ['email' => 'teacher1@demo.school'],
            ['name' => 'Mrs. Smith', 'password' => Hash::make('password'), 'role' => 'teacher']
        );

        $teacherUser2 = User::updateOrCreate(
            ['email' => 'teacher2@demo.school'],
            ['name' => 'Mr. Jones', 'password' => Hash::make('password'), 'role' => 'teacher']
        );

        $parentUser1 = User::updateOrCreate(
            ['email' => 'parent1@demo.school'],
            ['name' => 'Mr. and Mrs. Doe', 'password' => Hash::make('password'), 'role' => 'parent']
        );

        $parentUser2 = User::updateOrCreate(
            ['email' => 'parent2@demo.school'],
            ['name' => 'Mr. and Mrs. Smith', 'password' => Hash::make('password'), 'role' => 'parent']
        );

        $studentUser1 = User::updateOrCreate(
            ['email' => 'student1@demo.school'],
            ['name' => 'John Doe', 'password' => Hash::make('password'), 'role' => 'student']
        );

        $studentUser2 = User::updateOrCreate(
            ['email' => 'student2@demo.school'],
            ['name' => 'Jane Smith', 'password' => Hash::make('password'), 'role' => 'student']
        );

        $teacher1 = Teacher::updateOrCreate(
            ['employee_id' => 'T-1001'],
            ['user_id' => $teacherUser1->id, 'qualification' => 'B.Ed Mathematics']
        );

        $teacher2 = Teacher::updateOrCreate(
            ['employee_id' => 'T-1002'],
            ['user_id' => $teacherUser2->id, 'qualification' => 'B.Sc English']
        );

        $parent1 = ParentProfile::updateOrCreate(
            ['user_id' => $parentUser1->id],
            ['occupation' => 'Engineer', 'phone' => '08012345678']
        );

        $parent2 = ParentProfile::updateOrCreate(
            ['user_id' => $parentUser2->id],
            ['occupation' => 'Doctor', 'phone' => '08087654321']
        );

        $session = AcademicSession::updateOrCreate(
            ['name' => '2026/2027'],
            ['start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'is_current' => true]
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
            ['start_date' => '2027-04-15', 'end_date' => '2027-07-31', 'is_current' => false]
        );

        $class1 = SchoolClass::updateOrCreate(['name' => 'JSS 1'], ['form_teacher_id' => $teacher1->id]);
        $class2 = SchoolClass::updateOrCreate(['name' => 'JSS 2'], ['form_teacher_id' => $teacher2->id]);

        $math = Subject::firstOrCreate(['name' => 'Mathematics']);
        $english = Subject::firstOrCreate(['name' => 'English']);
        $science = Subject::firstOrCreate(['name' => 'Science']);

        $classSubject1 = ClassSubject::updateOrCreate(
            ['class_id' => $class1->id, 'subject_id' => $math->id],
            ['is_compulsory' => true]
        );

        $classSubject2 = ClassSubject::updateOrCreate(
            ['class_id' => $class1->id, 'subject_id' => $english->id],
            ['is_compulsory' => true]
        );

        $classSubject3 = ClassSubject::updateOrCreate(
            ['class_id' => $class2->id, 'subject_id' => $science->id],
            ['is_compulsory' => true]
        );

        TeacherClassSubject::updateOrCreate(
            ['teacher_id' => $teacher1->id, 'class_subject_id' => $classSubject1->id],
            ['is_active' => true]
        );

        TeacherClassSubject::updateOrCreate(
            ['teacher_id' => $teacher2->id, 'class_subject_id' => $classSubject2->id],
            ['is_active' => true]
        );

        ClassAssignment::updateOrCreate(
            ['teacher_id' => $teacher1->id, 'class_id' => $class1->id, 'academic_session_id' => $session->id, 'term_id' => $term1->id]
        );

        ClassAssignment::updateOrCreate(
            ['teacher_id' => $teacher2->id, 'class_id' => $class2->id, 'academic_session_id' => $session->id, 'term_id' => $term1->id]
        );

        $student1 = Student::updateOrCreate(
            ['user_id' => $studentUser1->id],
            ['admission_no' => 'ADM-001', 'class_id' => $class1->id, 'gender' => 'Male', 'status' => 'active']
        );

        $student2 = Student::updateOrCreate(
            ['user_id' => $studentUser2->id],
            ['admission_no' => 'ADM-002', 'class_id' => $class2->id, 'gender' => 'Female', 'status' => 'active']
        );

        $parent1->students()->sync([$student1->id, $student2->id]);
        $parent2->students()->sync([$student1->id]);

        $feeType = FeeType::updateOrCreate(
            ['name' => 'Tuition Fee', 'term_id' => $term1->id],
            ['amount' => 50000.00]
        );

        $studentFee = StudentFee::updateOrCreate(
            ['student_id' => $student1->id, 'fee_type_id' => $feeType->id, 'term_id' => $term1->id],
            ['amount_expected' => 50000.00, 'status' => 'paid']
        );

        $reportCard1 = ReportCard::updateOrCreate(
            ['student_id' => $student1->id, 'term_id' => $term1->id],
            ['is_published' => true, 'generated_at' => now()]
        );

        Result::updateOrCreate(
            ['student_id' => $student1->id, 'class_subject_id' => $classSubject1->id, 'term_id' => $term1->id],
            ['ca_score' => 30, 'exam_score' => 50, 'total' => 80, 'grade' => 'A1', 'is_locked' => true]
        );

        Result::updateOrCreate(
            ['student_id' => $student1->id, 'class_subject_id' => $classSubject2->id, 'term_id' => $term1->id],
            ['ca_score' => 28, 'exam_score' => 45, 'total' => 73, 'grade' => 'B2', 'is_locked' => true]
        );

        Attendance::updateOrCreate(
            ['student_id' => $student1->id, 'class_id' => $class1->id, 'term_id' => $term1->id, 'date' => '2026-09-15'],
            ['status' => 'present', 'marked_by' => $teacher1->id]
        );

        Announcement::updateOrCreate(
            ['title' => 'Welcome Back!'],
            ['body' => 'School resumes on September 1st, 2026.', 'target_role' => 'all', 'created_by' => $adminUser->id]
        );

        Announcement::updateOrCreate(
            ['title' => 'Parent Meeting'],
            ['body' => 'All parents are invited to the PTA meeting.', 'target_role' => 'parent', 'created_by' => $adminUser->id]
        );
    }
}
