<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\FeeType;
use App\Models\ParentProfile;
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

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_view_dashboard_and_see_linked_children(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM501',
        ]);

        $parent->students()->attach($student->id);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/dashboard');
        $response->assertOk();
        $response->assertSee('ADM501');
    }

    public function test_parent_cannot_view_unlinked_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);

        $otherParentUser = User::factory()->create(['role' => 'parent']);
        $otherParent = ParentProfile::create(['user_id' => $otherParentUser->id]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM502',
        ]);

        $otherParent->students()->attach($student->id);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/' . $student->id);
        $response->assertForbidden();
    }

    public function test_parent_sees_only_published_results(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);

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

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $subject = Subject::create(['name' => 'English']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM503',
        ]);

        $parent->students()->attach($student->id);

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

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/' . $student->id . '/results');
        $response->assertOk();
        $response->assertSee('First Term');
        $response->assertDontSee('Second Term');
    }

    public function test_parent_cannot_see_unpublished_results_for_unlinked_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);

        $otherParentUser = User::factory()->create(['role' => 'parent']);
        $otherParent = ParentProfile::create(['user_id' => $otherParentUser->id]);

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
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM504',
        ]);

        $otherParent->students()->attach($student->id);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/' . $student->id . '/results');
        $response->assertForbidden();
    }

    public function test_parent_sees_parent_and_all_announcements_only(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);

        $adminUser = User::factory()->create(['role' => 'admin']);

        $parentAnnouncement = Announcement::create([
            'title' => 'Parent Meeting',
            'body' => 'All parents must attend.',
            'target_role' => 'parent',
            'created_by' => $adminUser->id,
        ]);

        $teacherAnnouncement = Announcement::create([
            'title' => 'Staff Meeting',
            'body' => 'Teachers only.',
            'target_role' => 'teacher',
            'created_by' => $adminUser->id,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/announcements');
        $response->assertOk();
        $response->assertSee('Parent Meeting');
        $response->assertDontSee('Staff Meeting');
    }

    public function test_parent_can_view_child_attendance(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);

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
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM505',
        ]);

        $parent->students()->attach($student->id);

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $term->id,
            'date' => '2026-09-15',
            'status' => 'present',
            'marked_by' => $teacherUser->id,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/' . $student->id . '/attendance');
        $response->assertOk();
        $response->assertSee('present');
    }

    public function test_parent_can_view_child_fees(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);

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
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM506',
        ]);

        $parent->students()->attach($student->id);

        $feeType = FeeType::create([
            'name' => 'Tuition',
            'amount' => 50000,
            'term_id' => $term->id,
        ]);
        StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 50000,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/' . $student->id . '/fees');
        $response->assertOk();
        $response->assertSee('Tuition');
        $response->assertSee('50000');
    }
}
