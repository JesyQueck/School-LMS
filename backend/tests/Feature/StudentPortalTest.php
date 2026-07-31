<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_dashboard(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM601',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/dashboard');
        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_student_sees_only_published_results(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM602',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $publishedTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $unpublishedTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
            'start_date' => '2027-01-01',
            'end_date' => '2027-03-31',
            'is_current' => false,
        ]);

        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $publishedTerm->id,
            'is_published' => true,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $unpublishedTerm->id,
            'is_published' => false,
        ]);

        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $publishedTerm->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'grade' => 'A',
        ]);

        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $unpublishedTerm->id,
            'ca_score' => 25,
            'exam_score' => 45,
            'total' => 70,
            'grade' => 'B',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/results');
        $response->assertOk();
        $response->assertSee('First Term');
        $response->assertDontSee('Second Term');
    }

    public function test_student_can_view_attendance(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM603',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $term->id,
            'date' => '2026-09-15',
            'status' => 'present',
            'marked_by' => $teacherUser->id,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/attendance');
        $response->assertOk();
        $response->assertSee('Present');
    }

    public function test_student_can_view_fees(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM604',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $feeType = \App\Models\FeeType::create(['name' => 'Tuition', 'amount' => 50000, 'term_id' => $term->id]);
        StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 50000,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/fees');
        $response->assertOk();
        $response->assertSee('Tuition');
        $response->assertSee('50,000.00');
    }

    public function test_student_sees_student_and_all_announcements_only(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM605',
        ]);

        $adminUser = User::factory()->create(['role' => 'admin']);

        $studentAnnouncement = Announcement::create([
            'title' => 'Student Assembly',
            'body' => 'All students must attend.',
            'target_role' => 'student',
            'created_by' => $adminUser->id,
        ]);

        $teacherAnnouncement = Announcement::create([
            'title' => 'Staff Meeting',
            'body' => 'Teachers only.',
            'target_role' => 'teacher',
            'created_by' => $adminUser->id,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/announcements');
        $response->assertOk();
        $response->assertSee('Student Assembly');
        $response->assertDontSee('Staff Meeting');
    }

    public function test_student_can_view_published_report_cards(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM606',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'is_published' => true,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/report-cards');
        $response->assertOk();
        $response->assertSee('First Term');
    }
}
