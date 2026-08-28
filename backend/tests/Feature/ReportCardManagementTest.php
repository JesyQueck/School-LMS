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
use App\Services\ReportCardService;
use App\Services\ResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardManagementTest extends TestCase
{
    use RefreshDatabase;

    private function reportCardService(): ReportCardService
    {
        return app(ReportCardService::class);
    }

    private function createTestData(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);

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

        return compact('admin', 'session', 'term', 'class', 'subject', 'classSubject', 'student', 'result');
    }

    public function test_admin_can_create_and_publish_a_report_card_after_term_results_are_locked(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

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
        $this->assertDatabaseHas('report_cards', ['student_id' => $student->id, 'term_id' => $term->id, 'status' => ReportCard::STATUS_DRAFT]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $publishResponse = $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $publishResponse->assertRedirect('/admin/report-cards');
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id, 'status' => ReportCard::STATUS_PUBLISHED]);
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id, 'published_by' => $admin->id]);
        $this->assertNotNull($reportCard->refresh()->published_at);
    }

    public function test_admin_cannot_publish_a_report_card_when_term_results_are_not_locked(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        // Unlock the result so the term is not considered locked.
        $data['result']->update(['is_locked' => false]);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        $publishResponse = $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);

        $publishResponse->assertStatus(500);
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id, 'status' => ReportCard::STATUS_DRAFT]);
    }

    public function test_report_card_starts_as_draft(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $this->assertEquals(ReportCard::STATUS_DRAFT, $reportCard->status);
        $this->assertFalse($reportCard->isPublished());
    }

    public function test_admin_can_return_a_report_card(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $this->assertEquals(ReportCard::STATUS_DRAFT, $reportCard->status);

        $response = $this->post('/admin/report-cards/'.$reportCard->id.'/return', []);
        $response->assertRedirect('/admin/report-cards');

        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_RETURNED, $reportCard->status);
    }

    public function test_returned_report_card_can_be_corrected_and_re_approved(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        // Return for correction
        $this->post('/admin/report-cards/'.$reportCard->id.'/return', []);
        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_RETURNED, $reportCard->status);

        // Can still edit (returned → editable) — directly via service
        $this->reportCardService()->saveReportCard([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_teacher_remark' => 'Updated remark',
        ], ReportCard::where('student_id', $student->id)->where('term_id', $term->id)->first());

        $this->assertDatabaseHas('report_cards', ['student_id' => $student->id, 'class_teacher_remark' => 'Updated remark']);

        // Approve
        $this->post('/admin/report-cards/'.$reportCard->id.'/approve', []);
        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_APPROVED, $reportCard->status);

        // Publish (term results are locked)
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $reportCard->refresh();
        $this->assertTrue($reportCard->isPublished());
        $this->assertEquals(ReportCard::STATUS_PUBLISHED, $reportCard->status);
    }

    public function test_already_published_report_card_cannot_be_published_again(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        // First publish — should succeed
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $reportCard->refresh();
        $this->assertTrue($reportCard->isPublished());

        // Second publish attempt — should fail
        $response = $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $response->assertStatus(500);
    }

    public function test_published_report_card_cannot_be_edited(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        // Set original remark via service
        $this->reportCardService()->saveReportCard([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_teacher_remark' => 'Original remark',
        ], $reportCard);

        $reportCard->refresh();
        $this->assertEquals('Original remark', $reportCard->class_teacher_remark);

        // Publish
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $reportCard->refresh();

        // Attempt to edit — should fail with RuntimeException
        try {
            $this->reportCardService()->saveReportCard([
                'student_id' => $student->id,
                'term_id' => $term->id,
                'class_teacher_remark' => 'Modified remark',
            ], $reportCard);
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('published', $e->getMessage());
        }

        $reportCard->refresh();
        $this->assertEquals('Original remark', $reportCard->class_teacher_remark);
    }

    public function test_publishing_locks_related_results(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $result = $data['result'];
        $this->actingAs($admin);

        // Create a second subject for the student
        $subject2 = Subject::create(['name' => 'English']);
        $classSubject2 = ClassSubject::create([
            'class_id' => $data['class']->id,
            'subject_id' => $subject2->id,
            'is_compulsory' => true,
        ]);

        // Create a new result with is_locked=false
        $unlockedResult = Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject2->id,
            'term_id' => $term->id,
            'ca_score' => 30,
            'exam_score' => 60,
            'total' => 90,
            'grade' => 'A1',
            'remark' => 'Good',
            'submitted_by' => $admin->id,
            'is_locked' => false,
        ]);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        // Lock all results first
        $unlockedResult->update(['is_locked' => true]);

        // Now publish — should succeed because all results are locked
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $reportCard->refresh();
        $this->assertTrue($reportCard->isPublished());
    }

    public function test_locked_result_cannot_be_modified_via_service(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $result = $data['result'];
        $resultService = app(ResultService::class);

        // The result is already locked (created with is_locked=true)
        $result->refresh();
        $this->assertTrue($result->isLocked());

        // Attempting to update the locked result via the service should throw
        $this->expectException(\RuntimeException::class);

        $resultService->updateOrCreateResult([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 70,
        ], $admin);
    }

    public function test_publish_all_only_publishes_approved_cards(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $class = $data['class'];
        $classSubject = $data['classSubject'];
        $this->actingAs($admin);

        // Create a second student with a locked result
        $studentUser2 = User::factory()->create();
        $student2 = Student::create([
            'user_id' => $studentUser2->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM304',
        ]);

        Result::create([
            'student_id' => $student2->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 30,
            'exam_score' => 60,
            'total' => 90,
            'grade' => 'A1',
            'remark' => 'Excellent',
            'submitted_by' => $admin->id,
            'is_locked' => true,
        ]);

        // Student 1: approved (should be published via publishAll)
        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $rc1 = ReportCard::where('student_id', $student->id)->firstOrFail();
        $rc1->update(['status' => ReportCard::STATUS_APPROVED]);

        // Student 2: approved (should also be published via publishAll)
        $this->post('/admin/report-cards', [
            'student_id' => $student2->id,
            'term_id' => $term->id,
        ]);

        $rc2 = ReportCard::where('student_id', $student2->id)->firstOrFail();
        $rc2->update(['status' => ReportCard::STATUS_APPROVED]);

        $this->post('/admin/report-cards/publish-all/'.$term->id, []);

        $this->assertDatabaseHas('report_cards', ['student_id' => $student->id, 'status' => ReportCard::STATUS_PUBLISHED]);
        $this->assertDatabaseHas('report_cards', ['student_id' => $student2->id, 'status' => ReportCard::STATUS_PUBLISHED]);
    }

    public function test_publish_all_skips_draft_unpublished_with_unlocked_results(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        // Unlock the result
        $data['result']->update(['is_locked' => false]);

        // Create a report card
        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        // publishAll should fail because results are not locked
        $response = $this->post('/admin/report-cards/publish-all/'.$term->id, []);
        $response->assertStatus(500);

        $this->assertDatabaseHas('report_cards', ['student_id' => $student->id, 'status' => ReportCard::STATUS_DRAFT]);
    }

    public function test_publish_all_does_not_publish_already_published_cards(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        // Create and publish a report card
        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);

        // Try publishAll — should not error, just skip the already-published card
        $response = $this->post('/admin/report-cards/publish-all/'.$term->id, []);
        $response->assertRedirect('/admin/report-cards');

        $reportCard->refresh();
        $this->assertTrue($reportCard->isPublished());
        $this->assertEquals(ReportCard::STATUS_PUBLISHED, $reportCard->status);
    }

    public function test_admin_can_unpublish_a_published_report_card(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        // Publish
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $reportCard->refresh();
        $this->assertTrue($reportCard->isPublished());
        $this->assertNotNull($reportCard->published_at);
        $this->assertNotNull($reportCard->published_by);

        // Unpublish
        $response = $this->post('/admin/report-cards/'.$reportCard->id.'/unpublish', []);
        $response->assertRedirect('/admin/report-cards');

        $reportCard->refresh();
        $this->assertFalse($reportCard->isPublished());
        $this->assertNull($reportCard->published_by);
        $this->assertNull($reportCard->published_at);
        $this->assertEquals(ReportCard::STATUS_DRAFT, $reportCard->status);
    }

    public function test_unpublish_cannot_be_applied_to_draft_report_card(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $this->assertEquals(ReportCard::STATUS_DRAFT, $reportCard->status);

        $response = $this->post('/admin/report-cards/'.$reportCard->id.'/unpublish', []);
        $response->assertStatus(500);

        $reportCard->refresh();
        $this->assertFalse($reportCard->isPublished());
        $this->assertEquals(ReportCard::STATUS_DRAFT, $reportCard->status);
    }

    public function test_unpublish_cannot_be_applied_to_approved_report_card(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        $this->post('/admin/report-cards/'.$reportCard->id.'/approve', []);
        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_APPROVED, $reportCard->status);

        $response = $this->post('/admin/report-cards/'.$reportCard->id.'/unpublish', []);
        $response->assertStatus(500);
    }

    public function test_unpublish_unlocks_related_results(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $result = $data['result'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);

        $result->refresh();
        $this->assertTrue($result->is_locked);

        // Unpublish
        $this->post('/admin/report-cards/'.$reportCard->id.'/unpublish', []);

        $result->refresh();
        $this->assertFalse($result->is_locked);
    }

    public function test_locked_result_cannot_be_modified_via_direct_eloquent_update(): void
    {
        $data = $this->createTestData();
        $result = $data['result'];
        $result->refresh();

        $this->assertTrue($result->isLocked());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('locked');

        // Direct Eloquent update should be blocked by the model event
        $result->update(['ca_score' => 25]);
    }

    public function test_locked_result_can_be_unlocked_via_service(): void
    {
        $data = $this->createTestData();
        $result = $data['result'];
        $result->refresh();

        $this->assertTrue($result->isLocked());

        // Direct model update of is_locked should be allowed
        $result->update(['is_locked' => false]);
        $result->refresh();

        $this->assertFalse($result->isLocked());
    }

    public function test_publish_all_skips_draft_cards(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        // Create a draft report card (no approval)
        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $response = $this->post('/admin/report-cards/publish-all/'.$term->id, []);
        $response->assertRedirect('/admin/report-cards');

        // The draft report card should NOT be published
        $this->assertDatabaseHas('report_cards', ['student_id' => $student->id, 'status' => ReportCard::STATUS_DRAFT]);
    }

    public function test_student_sees_only_published_report_cards(): void
    {
        $data = $this->createTestData();
        $studentUser = $data['student']->user;
        $student = $data['student'];
        $term = $data['term'];
        $session = $data['session'];
        $this->actingAs($studentUser);

        // Create a published report card
        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        // Create a second term and a draft report card for it
        $term2 = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
            'start_date' => '2027-01-01',
            'end_date' => '2027-03-31',
            'is_current' => false,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term2->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $response = $this->get('/student/report-cards');
        $response->assertOk();
        $response->assertSee($term->name);
        $response->assertDontSee('Second Term');
    }

    public function test_student_cannot_view_another_student_report_card(): void
    {
        $data = $this->createTestData();
        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudent = Student::create([
            'user_id' => $otherStudentUser->id,
            'class_id' => $data['class']->id,
            'admission_no' => 'ADM999',
        ]);
        $this->actingAs($otherStudentUser);

        $reportCard = ReportCard::create([
            'student_id' => $data['student']->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => 'published',
        ]);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }

    public function test_student_cannot_download_unpublished_report_card(): void
    {
        $data = $this->createTestData();
        $studentUser = $data['student']->user;
        $this->actingAs($studentUser);

        $reportCard = ReportCard::create([
            'student_id' => $data['student']->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => 'draft',
        ]);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }

    public function test_pdf_generation_works_for_published_report(): void
    {
        $data = $this->createTestData();
        $studentUser = $data['student']->user;
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($studentUser);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $data['class']->id,
            'status' => 'published',
            'class_teacher_remark' => 'Well done',
            'position_in_class' => 1,
            'total_students_in_class' => 25,
        ]);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_report_card_uses_correct_session_and_class(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $session = $data['session'];
        $class = $data['class'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $this->assertEquals($student->id, $reportCard->student_id);
        $this->assertEquals($term->id, $reportCard->term_id);
        $this->assertEquals($class->id, $reportCard->class_id);
    }

    public function test_return_cannot_be_applied_to_published_report_card(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        // Publish
        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $reportCard->refresh();
        $this->assertTrue($reportCard->isPublished());

        // Attempt to return — should fail
        $response = $this->post('/admin/report-cards/'.$reportCard->id.'/return', []);
        $response->assertStatus(500);

        $reportCard->refresh();
        $this->assertTrue($reportCard->isPublished());
        $this->assertEquals(ReportCard::STATUS_PUBLISHED, $reportCard->status);
    }

    public function test_draft_report_card_is_not_published(): void
    {
        $data = $this->createTestData();

        $card = ReportCard::create([
            'student_id' => $data['student']->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->assertFalse($card->isPublished());
        $this->assertEquals(ReportCard::STATUS_DRAFT, $card->status);
    }

    public function test_approved_report_card_is_not_published(): void
    {
        $data = $this->createTestData();

        $card = ReportCard::create([
            'student_id' => $data['student']->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_APPROVED,
        ]);

        $this->assertFalse($card->isPublished());
        $this->assertEquals(ReportCard::STATUS_APPROVED, $card->status);
    }

    public function test_published_report_card_is_recognized_as_published(): void
    {
        $data = $this->createTestData();

        $card = ReportCard::create([
            'student_id' => $data['student']->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->assertTrue($card->isPublished());
        $this->assertEquals(ReportCard::STATUS_PUBLISHED, $card->status);
    }

    public function test_publish_method_changes_state_to_published(): void
    {
        $data = $this->createTestData();
        $admin = $data['admin'];
        $student = $data['student'];
        $term = $data['term'];
        $this->actingAs($admin);

        $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();

        $this->post('/admin/report-cards/'.$reportCard->id.'/approve', []);
        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_APPROVED, $reportCard->status);

        $this->post('/admin/report-cards/'.$reportCard->id.'/publish', []);
        $reportCard->refresh();

        $this->assertTrue($reportCard->isPublished());
        $this->assertEquals(ReportCard::STATUS_PUBLISHED, $reportCard->status);
    }

    public function test_unpublish_method_changes_state_back_to_draft(): void
    {
        $data = $this->createTestData();
        $service = $this->reportCardService();

        $card = ReportCard::create([
            'student_id' => $data['student']->id,
            'term_id' => $data['term']->id,
            'class_id' => $data['class']->id,
            'status' => ReportCard::STATUS_PUBLISHED,
            'published_by' => $data['admin']->id,
            'published_at' => now(),
        ]);

        $card = $service->unpublish($card);
        $card->refresh();

        $this->assertFalse($card->isPublished());
        $this->assertEquals(ReportCard::STATUS_DRAFT, $card->status);
        $this->assertNull($card->published_by);
        $this->assertNull($card->published_at);
    }
}
