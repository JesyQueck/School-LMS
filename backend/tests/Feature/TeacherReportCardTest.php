<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherReportCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUpTeacherWithClass(): array
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
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

        $class = SchoolClass::create(['name' => 'JSS 1']);
        ClassAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        return compact('teacherUser', 'teacher', 'session', 'term', 'class');
    }

    public function test_teacher_can_view_report_card_index(): void
    {
        $data = $this->setUpTeacherWithClass();
        $this->actingAs($data['teacherUser']);

        $response = $this->get('/teacher/report-cards');
        $response->assertOk();
    }

    public function test_teacher_report_cards_display_correctly_with_current_term(): void
    {
        $data = $this->setUpTeacherWithClass();
        $this->actingAs($data['teacherUser']);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-100',
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'class_teacher_remark' => 'Good student',
        ]);

        $response = $this->get('/teacher/report-cards');
        $response->assertOk();
        $response->assertViewHas('reportCards');
        $reportCards = $response->viewData('reportCards');
        $this->assertTrue($reportCards->contains('student_id', $student->id));
    }

    public function test_teacher_report_card_index_does_not_throw_when_no_current_term(): void
    {
        $data = $this->setUpTeacherWithClass();
        $data['term']->update(['is_current' => false]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get('/teacher/report-cards');
        $response->assertOk();
    }

    public function test_teacher_report_card_index_handles_no_class_assignment_gracefully(): void
    {
        $data = $this->setUpTeacherWithClass();
        $this->actingAs($data['teacherUser']);

        ClassAssignment::where('teacher_id', $data['teacher']->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->delete();

        $response = $this->get('/teacher/report-cards');
        $response->assertOk();
        $response->assertViewHas('reportCards');
        $this->assertCount(0, $response->viewData('reportCards'));
    }

    public function test_teacher_can_view_report_card_index_with_authorization(): void
    {
        $data = $this->setUpTeacherWithClass();
        $this->actingAs($data['teacherUser']);

        $otherClass = SchoolClass::create(['name' => 'JSS 10']);
        $otherClassStudentUser = User::factory()->create();
        $otherStudent = Student::create([
            'user_id' => $otherClassStudentUser->id,
            'class_id' => $otherClass->id,
            'admission_no' => 'ADM-RCT-101',
        ]);

        ReportCard::create([
            'student_id' => $otherStudent->id,
            'term_id' => $data['term']->id,
            'class_id' => $otherClass->id,
            'class_teacher_remark' => 'Should not appear',
        ]);

        $response = $this->get('/teacher/report-cards');
        $response->assertOk();
        $reportCards = $response->viewData('reportCards');
        $this->assertCount(0, $reportCards);
    }

    public function test_teacher_can_store_report_card_comment(): void
    {
        $data = $this->setUpTeacherWithClass();
        $this->actingAs($data['teacherUser']);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-1',
        ]);

        $response = $this->post('/teacher/report-cards', [
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'comment' => 'John is a good student.',
        ]);

        $response->assertRedirect('/teacher/report-cards');
        $this->assertDatabaseHas('report_cards', [
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_teacher_remark' => 'John is a good student.',
        ]);
    }

    public function test_teacher_can_get_submission_progress(): void
    {
        $data = $this->setUpTeacherWithClass();
        $this->actingAs($data['teacherUser']);

        $response = $this->get('/teacher/report-cards/progress');
        $response->assertOk();
        $response->assertJsonStructure([
            'class',
            'term',
            'total_students',
            'total_subjects',
            'submission_progress',
            'all_scores_submitted',
            'attendance_submitted',
            'comments_completed',
            'is_ready_to_submit',
        ]);
    }

    public function test_teacher_can_submit_report_card_with_scores_and_comments(): void
    {
        $data = $this->setUpTeacherWithClass();

        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $data['class']->id,
            'subject_id' => $subject->id,
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $data['teacher']->id,
            'class_subject_id' => $classSubject->id,
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-2',
        ]);

        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'grade' => 'B2',
            'is_locked' => true,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'class_teacher_remark' => 'Good student',
            'status' => ReportCard::STATUS_DRAFT,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($data['teacherUser']);

        $reportCard = ReportCard::where('student_id', $student->id)->first();

        $response = $this->post("/teacher/report-cards/{$reportCard->id}/submit");

        $response->assertRedirect('/teacher/report-cards');
        $this->assertDatabaseHas('report_cards', [
            'id' => $reportCard->id,
            'status' => ReportCard::STATUS_APPROVED,
        ]);
    }

    public function test_teacher_cannot_submit_report_card_without_scores(): void
    {
        $data = $this->setUpTeacherWithClass();

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-3',
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'class_teacher_remark' => 'Good student',
            'status' => ReportCard::STATUS_DRAFT,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($data['teacherUser']);

        $reportCard = ReportCard::where('student_id', $student->id)->first();

        $response = $this->post("/teacher/report-cards/{$reportCard->id}/submit");

        $response->assertRedirect();
        $this->assertDatabaseHas('report_cards', [
            'id' => $reportCard->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);
    }

    public function test_teacher_cannot_submit_report_card_without_comments(): void
    {
        $data = $this->setUpTeacherWithClass();

        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $data['class']->id,
            'subject_id' => $subject->id,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-4',
        ]);

        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'grade' => 'B2',
            'is_locked' => true,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_DRAFT,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($data['teacherUser']);

        $reportCard = ReportCard::where('student_id', $student->id)->first();

        $response = $this->post("/teacher/report-cards/{$reportCard->id}/submit");

        $response->assertRedirect();
        $this->assertDatabaseHas('report_cards', [
            'id' => $reportCard->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);
    }

    public function test_teacher_can_download_published_report_card(): void
    {
        $data = $this->setUpTeacherWithClass();

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-5',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get("/teacher/report-cards/{$reportCard->id}/download");
        $response->assertOk();
    }

    public function test_teacher_cannot_download_unpublished_report_card(): void
    {
        $data = $this->setUpTeacherWithClass();

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-6',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get("/teacher/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }

    public function test_teacher_can_submit_report_card_for_student_in_their_assigned_class(): void
    {
        $data = $this->setUpTeacherWithClass();

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-AUTH-1',
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->post('/teacher/report-cards', [
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'comment' => 'Good student.',
        ]);

        $response->assertRedirect('/teacher/report-cards');
        $this->assertDatabaseHas('report_cards', [
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_teacher_remark' => 'Good student.',
        ]);
    }

    public function test_teacher_cannot_submit_report_card_for_student_in_another_class(): void
    {
        $data = $this->setUpTeacherWithClass();

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $otherClass->id,
            'admission_no' => 'ADM-RCT-AUTH-2',
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->post('/teacher/report-cards', [
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'comment' => 'Should be blocked.',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('report_cards', [
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
        ]);
    }

    public function test_teacher_cannot_bypass_authorization_by_changing_student_id(): void
    {
        $data = $this->setUpTeacherWithClass();

        $this->actingAs($data['teacherUser']);

        $response = $this->post('/teacher/report-cards', [
            'student_id' => 99999,
            'term_id' => $data['term']->id,
            'comment' => 'Should be blocked.',
        ]);

        $response->assertSessionHasErrors(['student_id' => 'The selected student id is invalid.']);
        $this->assertDatabaseMissing('report_cards', [
            'student_id' => 99999,
            'term_id' => $data['term']->id,
        ]);
    }

    public function test_teacher_cannot_bypass_authorization_by_changing_term_id(): void
    {
        $data = $this->setUpTeacherWithClass();

        $otherTerm = Term::create([
            'academic_session_id' => $data['session']->id,
            'name' => 'Second Term',
            'start_date' => '2027-01-01',
            'end_date' => '2027-04-30',
            'is_current' => false,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-AUTH-4',
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->post('/teacher/report-cards', [
            'student_id' => $student->id,
            'term_id' => $otherTerm->id,
            'comment' => 'Should be blocked.',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('report_cards', [
            'student_id' => $student->id,
            'term_id' => $otherTerm->id,
        ]);
    }

    public function test_unauthorized_report_card_submission_returns_403(): void
    {
        $data = $this->setUpTeacherWithClass();

        $otherClass = SchoolClass::create(['name' => 'JSS 3']);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $otherClass->id,
            'admission_no' => 'ADM-RCT-AUTH-5',
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->post('/teacher/report-cards', [
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'comment' => 'Should be blocked.',
        ]);

        $response->assertStatus(403);
    }

    public function test_teacher_with_assignment_for_one_term_cannot_submit_for_another_term(): void
    {
        $data = $this->setUpTeacherWithClass();

        $otherTerm = Term::create([
            'academic_session_id' => $data['session']->id,
            'name' => 'Second Term',
            'start_date' => '2027-01-01',
            'end_date' => '2027-04-30',
            'is_current' => false,
        ]);

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        ClassAssignment::create([
            'teacher_id' => $data['teacher']->id,
            'class_id' => $otherClass->id,
            'academic_session_id' => $data['session']->id,
            'term_id' => $otherTerm->id,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-RCT-AUTH-6',
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->post('/teacher/report-cards', [
            'student_id' => $student->id,
            'term_id' => $otherTerm->id,
            'comment' => 'Should be blocked.',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('report_cards', [
            'student_id' => $student->id,
            'term_id' => $otherTerm->id,
        ]);
    }

    public function test_teacher_can_download_published_report_card_for_their_assigned_class(): void
    {
        $data = $this->setUpTeacherWithClass();

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-DL-1',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get("/teacher/report-cards/{$reportCard->id}/download");

        $response->assertOk();
    }

    public function test_teacher_cannot_download_published_report_card_belonging_to_another_class(): void
    {
        $data = $this->setUpTeacherWithClass();

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $otherClass->id,
            'admission_no' => 'ADM-DL-2',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $otherClass->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get("/teacher/report-cards/{$reportCard->id}/download");

        $response->assertForbidden();
    }

    public function test_unauthorized_download_returns_403(): void
    {
        $data = $this->setUpTeacherWithClass();

        $otherClass = SchoolClass::create(['name' => 'JSS 3']);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $otherClass->id,
            'admission_no' => 'ADM-DL-3',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $otherClass->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get("/teacher/report-cards/{$reportCard->id}/download");

        $response->assertStatus(403);
    }

    public function test_unpublished_report_cards_remain_inaccessible(): void
    {
        $data = $this->setUpTeacherWithClass();

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM-DL-4',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get("/teacher/report-cards/{$reportCard->id}/download");

        $response->assertForbidden();
    }

    public function test_download_authorization_cannot_be_bypassed_by_changing_report_card_id(): void
    {
        $data = $this->setUpTeacherWithClass();

        $this->actingAs($data['teacherUser']);

        $response = $this->get('/teacher/report-cards/99999/download');

        $response->assertNotFound();
    }
}
