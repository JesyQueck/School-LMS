<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\FeeType;
use App\Models\ParentProfile;
use App\Models\Payment;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_school_models_can_be_created_with_relationships(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $session = AcademicSession::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'first',
            'start_date' => '2024-09-01',
            'end_date' => '2024-12-20',
            'is_current' => true,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'JSS1A',
            'form_teacher_id' => null,
        ]);

        $subject = Subject::create([
            'name' => 'Mathematics',
        ]);

        $classSubject = ClassSubject::create([
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'is_compulsory' => true,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'employee_id' => 'TCH-001',
            'qualification' => 'B.Sc. Mathematics',
            'phone' => '08012345678',
        ]);

        $teacherClassSubject = TeacherClassSubject::create([
            'teacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
            'is_active' => true,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'admission_no' => 'ADM-001',
            'class_id' => $schoolClass->id,
            'house' => 'Blue',
            'gender' => 'male',
            'state_of_origin' => 'Lagos',
            'date_of_birth' => '2012-04-10',
            'blood_group' => 'O+',
            'emergency_contact' => 'Jane Doe',
            'emergency_phone' => '08087654321',
            'status' => 'active',
        ]);

        $parent = ParentProfile::create([
            'user_id' => $user->id,
            'occupation' => 'Engineer',
            'phone' => '08000000000',
        ]);

        $parent->students()->attach($student->id);

        $feeType = FeeType::create([
            'name' => 'Tuition',
            'amount' => 25000.00,
            'term_id' => $term->id,
            'class_id' => $schoolClass->id,
        ]);

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 25000.00,
            'status' => 'unpaid',
        ]);

        $payment = Payment::create([
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-001',
            'amount_paid' => 10000.00,
            'payment_method' => 'cash',
            'payment_date' => '2024-10-01',
            'recorded_by' => $user->id,
        ]);

        $result = Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 18.5,
            'exam_score' => 61.0,
            'total' => 79.5,
            'grade' => 'A',
            'remark' => 'Excellent',
            'submitted_by' => $user->id,
            'is_locked' => false,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $schoolClass->id,
            'term_id' => $term->id,
            'date' => '2024-10-02',
            'status' => 'present',
            'marked_by' => $user->id,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_teacher_remark' => 'Good progress',
            'principal_remark' => 'Promising student',
            'position_in_class' => 2,
            'total_students_in_class' => 20,
            'next_term_begins' => '2025-01-06',
            'is_published' => false,
            'generated_at' => now(),
        ]);

        $this->assertDatabaseHas('academic_sessions', ['name' => '2024/2025']);
        $this->assertTrue($session->terms()->exists());
        $this->assertTrue($schoolClass->students()->exists());
        $this->assertTrue($student->results()->exists());
        $this->assertTrue($student->payments()->exists());
        $this->assertTrue($teacherClassSubject->teacher()->exists());
        $this->assertTrue($parent->students()->exists());
        $this->assertTrue($term->results()->exists());
        $this->assertNotNull($payment->receipt_number);
        $this->assertSame('A', $result->grade);
    }
}
