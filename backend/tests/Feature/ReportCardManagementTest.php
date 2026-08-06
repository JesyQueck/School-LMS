<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_publish_a_report_card_after_term_results_are_locked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

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

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_compulsory' => true,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM303',
        ]);

        // Create and lock a result so the term is considered locked.
        $result = Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 20,
            'exam_score' => 55,
            'total' => 75,
            'grade' => 'A1',
            'remark' => 'Excellent',
            'submitted_by' => $admin->id,
            'is_locked' => true,
        ]);

        $response = $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_teacher_remark' => 'Promoted',
            'principal_remark' => 'Keep it up',
            'position_in_class' => 2,
            'total_students_in_class' => 30,
            'next_term_begins' => '2027-01-10',
        ]);

        $response->assertRedirect('/admin/report-cards');
        $this->assertDatabaseHas('report_cards', ['student_id' => $student->id, 'term_id' => $term->id, 'is_published' => false]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $publishResponse = $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $publishResponse->assertRedirect('/admin/report-cards');
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id, 'is_published' => true]);
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id, 'published_by' => $admin->id]);
        $this->assertNotNull($reportCard->refresh()->published_at);
    }

    public function test_admin_cannot_publish_a_report_card_when_term_results_are_not_locked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

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

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $subject = Subject::create(['name' => 'English']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_compulsory' => true,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM304',
        ]);

        // Create an UNLOCKED result so the term is not considered locked.
        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 20,
            'exam_score' => 55,
            'total' => 75,
            'grade' => 'A1',
            'remark' => 'Excellent',
            'submitted_by' => $admin->id,
            'is_locked' => false,
        ]);

        $response = $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        // Publishing should fail because the term has an unlocked result.
        $publishResponse = $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);

        $publishResponse->assertStatus(500);
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id, 'is_published' => false]);
    }
}
