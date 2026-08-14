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
            'is_published' => false,
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
            'is_published' => false,
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
            'is_published' => false,
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
            'is_published' => true,
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
            'is_published' => false,
        ]);

        $this->actingAs($data['teacherUser']);

        $response = $this->get("/teacher/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }
}
