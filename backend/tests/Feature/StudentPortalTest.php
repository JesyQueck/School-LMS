<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\FeeType;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
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

    public function test_student_cannot_access_removed_results_page(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM602',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/results');
        $response->assertNotFound();
    }

    public function test_student_cannot_access_removed_assignments_page(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM603',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/assignments');
        $response->assertNotFound();
    }

    public function test_student_dashboard_does_not_show_assignment_or_results_links(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM604',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/dashboard');
        $response->assertSee('Report Cards');
        $response->assertDontSee('My Results');
        $response->assertDontSee('Assignments');
        $response->assertDontSee('Upcoming Assignments');
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

        $feeType = FeeType::create(['name' => 'Tuition', 'amount' => 50000, 'term_id' => $term->id]);
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
            'class_teacher_remark' => 'Good student',
            'position_in_class' => 3,
            'total_students_in_class' => 40,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/report-cards');
        $response->assertOk();
        $response->assertSee('2026/2027');
        $response->assertSee('First Term');
        $response->assertSee('JSS 1');
        $response->assertSee('Published');
        $response->assertSee('3 of 40');
    }

    public function test_student_cannot_view_unpublished_report_cards(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM607',
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
            'is_published' => false,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/report-cards');
        $response->assertOk();
        $response->assertDontSee('First Term');
    }

    public function test_student_cannot_download_unpublished_report_card(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM608',
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

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'is_published' => false,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }

    public function test_student_cannot_view_another_student_report_card(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM609',
        ]);

        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudent = Student::create([
            'user_id' => $otherStudentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM610',
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

        $reportCard = ReportCard::create([
            'student_id' => $otherStudent->id,
            'term_id' => $term->id,
            'is_published' => true,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }
}
