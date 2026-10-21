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
use App\Models\Timetable;
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
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $parent->students()->attach($student->id);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/dashboard');
        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertSee('ADM501');
    }

    public function test_parent_sidebar_links_to_existing_parent_features(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-SIDEBAR',
        ]);
        $parent->students()->attach($student->id);
        $this->actingAs($parentUser);

        $response = $this->get(route('parent.dashboard'));

        $response->assertOk();
        foreach ([
            'Dashboard' => route('parent.dashboard'),
            'Children' => route('parent.dashboard').'#my-children',
            'Timetable' => route('parent.timetable'),
            'Results' => route('parent.children.results', $student),
            'Attendance' => route('parent.children.attendance', $student),
            'Fees' => route('parent.children.fees', $student),
            'Report Cards' => route('parent.children.report-cards', $student),
            'Announcements' => route('parent.announcements'),
            'Profile' => route('settings.profile'),
            'Change Password' => route('password.change'),
        ] as $label => $href) {
            $response->assertSee($label);
            $response->assertSee('href="'.$href.'"', false);
        }
    }

    public function test_parent_can_select_each_linked_child_for_dashboard_summaries(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $children = collect([
            ['name' => 'First Child', 'admission_no' => 'ADM-SEL-1'],
            ['name' => 'Second Child', 'admission_no' => 'ADM-SEL-2'],
        ])->map(function (array $data) use ($parent, $class) {
            $child = Student::create([
                'user_id' => User::factory()->create(['name' => $data['name']])->id,
                'class_id' => $class->id,
                'admission_no' => $data['admission_no'],
                'first_name' => $data['name'],
            ]);
            $parent->students()->attach($child->id);

            return $child;
        });
        $this->actingAs($parentUser);

        foreach ($children as $child) {
            $response = $this->get(route('parent.dashboard', ['student' => $child->id]));

            $response->assertOk();
            $this->assertSame($child->id, $response->viewData('selectedChild')->id);
            $response->assertSee('Showing summaries for this linked child only.');
            $response->assertSee($child->admission_no);
        }
    }

    public function test_parent_averages_exclude_children_without_records_and_selected_child_results_remain_isolated(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $class = SchoolClass::create(['name' => 'Parent Metric Scope Class']);
        $children = collect([
            ['name' => 'Child With Records', 'admission' => 'ADM-PARENT-RECORDED'],
            ['name' => 'Child Without Records', 'admission' => 'ADM-PARENT-MISSING'],
        ])->map(function (array $data) use ($parent, $class) {
            $child = Student::create([
                'user_id' => User::factory()->create()->id,
                'class_id' => $class->id,
                'admission_no' => $data['admission'],
                'first_name' => $data['name'],
            ]);
            $parent->students()->attach($child->id);

            return $child;
        });
        $session = AcademicSession::create([
            'name' => 'Parent Metrics Session',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Parent Metrics Published Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
        ]);
        ReportCard::create([
            'student_id' => $children[0]->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);
        $subject = Subject::create(['name' => 'Isolated Published Subject']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
        Result::create([
            'student_id' => $children[0]->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 24,
            'exam_score' => 56,
        ]);
        Attendance::create([
            'student_id' => $children[0]->id,
            'class_id' => $class->id,
            'term_id' => $term->id,
            'date' => '2026-09-15',
            'status' => 'present',
            'marked_by' => User::factory()->create(['role' => 'teacher'])->id,
        ]);

        $this->actingAs($parentUser);

        $withRecords = $this->get(route('parent.dashboard', ['student' => $children[0]->id]));
        $withRecords->assertOk();
        $this->assertSame(100.0, $withRecords->viewData('stats')['attendance']);
        $this->assertSame(80.0, $withRecords->viewData('stats')['avg_result']);
        $this->assertSame(1, $withRecords->viewData('stats')['attendance_children']);
        $this->assertSame(1, $withRecords->viewData('stats')['result_children']);
        $this->assertSame($children[0]->id, $withRecords->viewData('selectedChild')->id);
        $this->assertCount(1, $withRecords->viewData('resultTrend')['rows']);
        $withRecords->assertSee('One published term is available; a trend cannot be inferred.');
        $withRecords->assertSee('Isolated Published Subject');

        $withoutRecords = $this->get(route('parent.dashboard', ['student' => $children[1]->id]));
        $withoutRecords->assertOk();
        $this->assertSame($children[1]->id, $withoutRecords->viewData('selectedChild')->id);
        $this->assertNull($withoutRecords->viewData('children')->firstWhere('id', $children[1]->id)->metrics['attendance_rate']);
        $this->assertNull($withoutRecords->viewData('children')->firstWhere('id', $children[1]->id)->metrics['average_score']);
        $this->assertCount(0, $withoutRecords->viewData('recentResults'));
        $withoutRecords->assertDontSee('Isolated Published Subject');
        $withoutRecords->assertSee('Household overview');
        $this->assertSame([], $withoutRecords->viewData('resultTrend')['rows']);
    }

    public function test_parent_recent_report_cards_use_academic_chronology_and_latest_card_per_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $class = SchoolClass::create(['name' => 'Parent Report Chronology Class']);
        $children = collect(['Recent Child', 'Earlier Child'])->map(function (string $name) use ($parent, $class) {
            $child = Student::create([
                'user_id' => User::factory()->create()->id,
                'class_id' => $class->id,
                'admission_no' => 'ADM-REPORT-'.str_replace(' ', '-', $name),
                'first_name' => $name,
            ]);
            $parent->students()->attach($child->id);

            return $child;
        });
        $newerSession = AcademicSession::create([
            'name' => 'Chronology Newer Session',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $olderSession = AcademicSession::create([
            'name' => 'Chronology Older Session',
            'start_date' => '2024-09-01',
            'end_date' => '2025-07-31',
        ]);
        $newerTerm = Term::create([
            'academic_session_id' => $newerSession->id,
            'name' => 'Chronologically Newest Report Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
        ]);
        $olderTerm = Term::create([
            'academic_session_id' => $olderSession->id,
            'name' => 'Chronologically Older Report Term',
            'start_date' => '2024-09-01',
            'end_date' => '2024-12-20',
        ]);

        foreach ([
            ['student' => $children[0], 'term' => $newerTerm],
            ['student' => $children[0], 'term' => $olderTerm],
            ['student' => $children[1], 'term' => $olderTerm],
        ] as $entry) {
            ReportCard::create([
                'student_id' => $entry['student']->id,
                'term_id' => $entry['term']->id,
                'class_id' => $class->id,
                'status' => ReportCard::STATUS_PUBLISHED,
            ]);
        }

        $response = $this->actingAs($parentUser)->get(route('parent.dashboard'));

        $response->assertOk();
        $reportCards = $response->viewData('recentReportCards');
        $this->assertCount(2, $reportCards);
        $this->assertSame($newerTerm->id, $reportCards[0]['reportCard']->term_id);
        $this->assertSame($children[0]->id, $reportCards[0]['student']->id);
        $this->assertSame($olderTerm->id, $reportCards[1]['reportCard']->term_id);
        $response->assertSee('Chronologically Newest Report Term');
        $response->assertSee('No attendance records');
    }

    public function test_parent_dashboard_shows_an_empty_state_when_no_report_cards_are_published(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $class = SchoolClass::create(['name' => 'Parent No Report Class']);
        $child = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-PARENT-NO-REPORT',
        ]);
        $parent->students()->attach($child->id);

        $response = $this->actingAs($parentUser)->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('No published report cards');
        $this->assertCount(0, $response->viewData('recentReportCards'));
    }

    public function test_parent_cannot_select_a_child_linked_to_another_parent(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);
        $otherParentUser = User::factory()->create(['role' => 'parent']);
        $otherParent = ParentProfile::create(['user_id' => $otherParentUser->id]);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $unlinkedChild = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-FORGED',
        ]);
        $otherParent->students()->attach($unlinkedChild->id);
        $this->actingAs($parentUser);

        $response = $this->get(route('parent.dashboard', ['student' => $unlinkedChild->id]));

        $response->assertRedirect();
        $response->assertSessionHasErrors('student');
        $response->assertSessionMissing('selectedChild');
    }

    public function test_parent_dashboard_shows_empty_state_when_no_children_are_linked(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);
        $this->actingAs($parentUser);

        $response = $this->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('No children linked');
        $response->assertSee('Their published results, attendance, and fee summaries will appear here.');
        $this->assertNull($response->viewData('selectedChild'));
    }

    public function test_dashboard_recent_results_are_chronological_and_only_published_terms_are_shown(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $studentClass = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $studentClass->id,
            'admission_no' => 'ADM-CHRONO',
        ]);
        $parent->students()->attach($student->id);
        $sibling = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $studentClass->id,
            'admission_no' => 'ADM-CHRONO-SIBLING',
        ]);
        $parent->students()->attach($sibling->id);

        $olderSession = AcademicSession::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-07-31',
        ]);
        $newerSession = AcademicSession::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
        ]);

        $terms = collect([
            ['session' => $olderSession, 'name' => 'Oldest Published Term', 'start' => '2024-01-01', 'subject' => 'Oldest Published Subject', 'status' => ReportCard::STATUS_PUBLISHED, 'score' => 42],
            ['session' => $olderSession, 'name' => 'Fourth Published Term', 'start' => '2024-05-01', 'subject' => 'Fourth Published Subject', 'status' => ReportCard::STATUS_PUBLISHED, 'score' => 55],
            ['session' => $olderSession, 'name' => 'Old Term', 'start' => '2024-09-01', 'subject' => 'Old Published Subject', 'status' => ReportCard::STATUS_PUBLISHED, 'score' => 61],
            ['session' => $olderSession, 'name' => 'Middle Term', 'start' => '2025-01-01', 'subject' => 'Middle Published Subject', 'status' => ReportCard::STATUS_PUBLISHED, 'score' => 72],
            ['session' => $newerSession, 'name' => 'Latest Term', 'start' => '2025-09-01', 'subject' => 'Latest Published Subject', 'status' => ReportCard::STATUS_PUBLISHED, 'score' => 83],
            ['session' => $newerSession, 'name' => 'Unpublished Term', 'start' => '2026-01-01', 'subject' => 'Unpublished Subject', 'status' => ReportCard::STATUS_DRAFT, 'score' => 99],
        ])->map(function (array $data) use ($student, $studentClass) {
            $term = Term::create([
                'academic_session_id' => $data['session']->id,
                'name' => $data['name'],
                'start_date' => $data['start'],
                'end_date' => $data['start'],
            ]);
            ReportCard::create([
                'student_id' => $student->id,
                'term_id' => $term->id,
                'class_id' => $studentClass->id,
                'status' => $data['status'],
            ]);
            $subject = Subject::create(['name' => $data['subject']]);
            $classSubject = ClassSubject::create([
                'class_id' => $studentClass->id,
                'subject_id' => $subject->id,
            ]);
            Result::create([
                'student_id' => $student->id,
                'class_subject_id' => $classSubject->id,
                'term_id' => $term->id,
                'ca_score' => min(30, $data['score']),
                'exam_score' => max(0, $data['score'] - 30),
                'total' => $data['score'],
            ]);

            return $data['subject'];
        });
        $siblingTerm = Term::create([
            'academic_session_id' => $newerSession->id,
            'name' => 'Sibling Private Term',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-01',
        ]);
        ReportCard::create([
            'student_id' => $sibling->id,
            'term_id' => $siblingTerm->id,
            'class_id' => $studentClass->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);
        $siblingSubject = Subject::create(['name' => 'Sibling Private Subject']);
        $siblingClassSubject = ClassSubject::create([
            'class_id' => $studentClass->id,
            'subject_id' => $siblingSubject->id,
        ]);
        Result::create([
            'student_id' => $sibling->id,
            'class_subject_id' => $siblingClassSubject->id,
            'term_id' => $siblingTerm->id,
            'ca_score' => 30,
            'exam_score' => 70,
        ]);
        $this->actingAs($parentUser);

        $response = $this->get(route('parent.dashboard', ['student' => $student->id]));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Latest Published Subject',
            'Middle Published Subject',
            'Old Published Subject',
        ]);
        $response->assertDontSee('Unpublished Subject');
        $this->assertSame([
            'Fourth Published Term · 2024/2025',
            'Old Term · 2024/2025',
            'Middle Term · 2024/2025',
            'Latest Term · 2025/2026',
        ], $response->viewData('resultTrend')['labels']);
        $this->assertSame([55.0, 61.0, 72.0, 83.0], $response->viewData('resultTrend')['averages']);
        $this->assertNotContains('Oldest Published Term · 2024/2025', $response->viewData('resultTrend')['labels']);
        $response->assertDontSee('Sibling Private Subject');
        $this->assertCount(4, $response->viewData('resultTrend')['rows']);
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

        $response = $this->get('/parent/children/'.$student->id);
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
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $unpublishedTerm->id,
            'status' => ReportCard::STATUS_DRAFT,
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

        $response = $this->get('/parent/children/'.$student->id.'/results');
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

        $response = $this->get('/parent/children/'.$student->id.'/results');
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

        $response = $this->get('/parent/children/'.$student->id.'/attendance');
        $response->assertOk();
        $response->assertSee('Present');
        $response->assertSee('Current Term Attendance');
        $response->assertSee('First Term');
    }

    public function test_parent_attendance_is_limited_to_the_configured_current_term(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $currentTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Current Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);
        $pastTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Past Term',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-20',
        ]);
        $class = SchoolClass::create(['name' => 'Attendance Current Class']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-CURRENT-ATTENDANCE',
            'first_name' => 'Current',
            'last_name' => 'Student',
        ]);
        $parent->students()->attach($student->id);
        $teacher = User::factory()->create(['role' => 'teacher']);

        foreach ([
            ['term_id' => $currentTerm->id, 'date' => '2026-09-15'],
            ['term_id' => $pastTerm->id, 'date' => '2026-02-15'],
        ] as $record) {
            Attendance::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'term_id' => $record['term_id'],
                'date' => $record['date'],
                'status' => 'present',
                'marked_by' => $teacher->id,
            ]);
        }

        $this->actingAs($parentUser);

        $response = $this->get(route('parent.children.attendance', $student));

        $response->assertOk();
        $response->assertSee('Current Student');
        $response->assertSee('Current Term');
        $response->assertDontSee('Past Term');
        $this->assertSame(1, $response->viewData('attendance')->count());
    }

    public function test_parent_attendance_without_a_current_term_is_labeled_as_history(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Historical Term',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-20',
        ]);
        $class = SchoolClass::create(['name' => 'Attendance History Class']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-HISTORY-ATTENDANCE',
        ]);
        $parent->students()->attach($student->id);
        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $term->id,
            'date' => '2026-02-15',
            'status' => 'present',
            'marked_by' => User::factory()->create(['role' => 'teacher'])->id,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get(route('parent.children.attendance', $student));

        $response->assertOk();
        $response->assertSee('Attendance History');
        $response->assertSee('Showing all recorded periods. No current term is configured.');
        $response->assertSee('Historical Term');
        $this->assertSame(1, $response->viewData('attendance')->count());
        $this->assertNull($response->viewData('currentTerm'));
    }

    public function test_parent_attendance_distinguishes_no_current_period_records_from_no_attendance_history(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $currentTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Current Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);
        $pastTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Past Term',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-20',
        ]);
        $class = SchoolClass::create(['name' => 'Attendance Empty Class']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-EMPTY-CURRENT-ATTENDANCE',
        ]);
        $parent->students()->attach($student->id);
        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $pastTerm->id,
            'date' => '2026-02-15',
            'status' => 'present',
            'marked_by' => User::factory()->create(['role' => 'teacher'])->id,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get(route('parent.children.attendance', $student));

        $response->assertOk();
        $response->assertSee('No attendance records found for Current Term.');
        $response->assertSee('Attendance records exist for this child in other periods.');
        $this->assertSame(0, $response->viewData('attendance')->count());
    }

    public function test_parent_cannot_view_attendance_for_an_unlinked_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);
        $otherParent = ParentProfile::create([
            'user_id' => User::factory()->create(['role' => 'parent'])->id,
        ]);
        $class = SchoolClass::create(['name' => 'Unlinked Attendance Class']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-UNLINKED-ATTENDANCE',
        ]);
        $otherParent->students()->attach($student->id);

        $this->actingAs($parentUser);

        $this->get(route('parent.children.attendance', $student))->assertForbidden();
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

        $response = $this->get('/parent/children/'.$student->id.'/fees');
        $response->assertOk();
        $response->assertSee('Tuition');
        $response->assertSee('50,000.00');
    }

    public function test_parent_cannot_view_fees_for_an_unlinked_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);
        $otherParent = ParentProfile::create([
            'user_id' => User::factory()->create(['role' => 'parent'])->id,
        ]);
        $class = SchoolClass::create(['name' => 'Unlinked Fees Class']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-UNLINKED-FEES',
        ]);
        $otherParent->students()->attach($student->id);

        $this->actingAs($parentUser);

        $this->get(route('parent.children.fees', $student))->assertForbidden();
    }

    public function test_parent_timetable_selection_is_limited_to_linked_children(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $otherParent = ParentProfile::create([
            'user_id' => User::factory()->create(['role' => 'parent'])->id,
        ]);
        $linkedClass = SchoolClass::create(['name' => 'Linked Timetable Class']);
        $unlinkedClass = SchoolClass::create(['name' => 'Unlinked Timetable Class']);
        $linkedChild = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $linkedClass->id,
            'admission_no' => 'ADM-LINKED-TIMETABLE',
            'first_name' => 'Linked',
            'last_name' => 'Child',
        ]);
        $unlinkedChild = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $unlinkedClass->id,
            'admission_no' => 'ADM-UNLINKED-TIMETABLE',
            'first_name' => 'Unlinked',
            'last_name' => 'Child',
        ]);
        $parent->students()->attach($linkedChild->id);
        $otherParent->students()->attach($unlinkedChild->id);

        foreach ([
            ['class' => $linkedClass, 'name' => 'Linked Subject'],
            ['class' => $unlinkedClass, 'name' => 'Unlinked Subject'],
        ] as $entry) {
            $subject = Subject::create(['name' => $entry['name']]);
            $classSubject = ClassSubject::create([
                'class_id' => $entry['class']->id,
                'subject_id' => $subject->id,
            ]);
            Timetable::create([
                'class_subject_id' => $classSubject->id,
                'day' => 'Monday',
                'start_time' => '08:00:00',
                'end_time' => '08:45:00',
            ]);
        }

        $this->actingAs($parentUser);

        $selectedResponse = $this->get(route('parent.timetable', ['student' => $linkedChild->id]));
        $selectedResponse->assertOk();
        $this->assertSame($linkedChild->id, $selectedResponse->viewData('selected')->id);
        $selectedResponse->assertSee('Linked Subject');
        $selectedResponse->assertDontSee('Unlinked Subject');

        $forgedResponse = $this->get(route('parent.timetable', ['student' => $unlinkedChild->id]));
        $forgedResponse->assertOk();
        $this->assertSame($linkedChild->id, $forgedResponse->viewData('selected')->id);
        $forgedResponse->assertSee('Linked Subject');
        $forgedResponse->assertDontSee('Unlinked Subject');
    }

    public function test_parent_can_view_report_cards_for_linked_child(): void
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
            'admission_no' => 'ADM601',
        ]);

        $parent->students()->attach($student->id);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/'.$student->id.'/report-cards');
        $response->assertOk();
        $response->assertSee('First Term');
    }

    public function test_parent_cannot_view_report_cards_for_unlinked_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);

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
            'admission_no' => 'ADM602',
        ]);

        $otherParent->students()->attach($student->id);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/'.$student->id.'/report-cards');
        $response->assertForbidden();
    }

    public function test_parent_can_download_published_report_card_for_linked_child(): void
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
            'admission_no' => 'ADM603',
        ]);

        $parent->students()->attach($student->id);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/'.$student->id.'/report-cards/'.$reportCard->id.'/download');
        $response->assertOk();
    }

    public function test_parent_cannot_download_unpublished_report_card(): void
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
            'admission_no' => 'ADM604',
        ]);

        $parent->students()->attach($student->id);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/children/'.$student->id.'/report-cards/'.$reportCard->id.'/download');
        $response->assertForbidden();
    }

    public function test_parent_cannot_download_a_linked_report_card_for_an_unlinked_child(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);
        $otherParent = ParentProfile::create([
            'user_id' => User::factory()->create(['role' => 'parent'])->id,
        ]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Published Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
        ]);
        $class = SchoolClass::create(['name' => 'Unlinked Report Card Class']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-UNLINKED-REPORT-CARD',
        ]);
        $otherParent->students()->attach($student->id);
        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($parentUser);

        $this->get(route('parent.children.report-cards.download', [$student, $reportCard]))
            ->assertForbidden();
    }

    public function test_parent_cannot_download_another_linked_childs_report_card_using_the_wrong_child_route(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Published Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
        ]);
        $class = SchoolClass::create(['name' => 'Shared Report Card Class']);
        $children = collect(['First', 'Second'])->map(function (string $name) use ($parent, $class) {
            $student = Student::create([
                'user_id' => User::factory()->create()->id,
                'class_id' => $class->id,
                'admission_no' => 'ADM-REPORT-'.$name,
            ]);
            $parent->students()->attach($student->id);

            return $student;
        });
        $reportCard = ReportCard::create([
            'student_id' => $children[1]->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($parentUser);

        $this->get(route('parent.children.report-cards.download', [$children[0], $reportCard]))
            ->assertForbidden();
    }
}
