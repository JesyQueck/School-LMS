<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\FeeType;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Timetable;
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
        $response->assertSee('No results');
        $this->assertNull($response->viewData('averageScore'));
        $this->assertSame(0, $response->viewData('subjectCount'));
        $this->assertSame([], $response->viewData('resultTrend')['rows']);
        $response->assertSee('No published results are available to chart yet.');
    }

    public function test_student_dashboard_averages_only_published_scored_results_and_orders_report_cards_by_academic_dates(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'Student Dashboard Results Class']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-STUDENT-DASH-RESULTS',
        ]);
        $newerSession = AcademicSession::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
        ]);
        $olderSession = AcademicSession::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-07-31',
        ]);
        $newerTerm = Term::create([
            'academic_session_id' => $newerSession->id,
            'name' => 'Chronologically Newer Term',
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-20',
        ]);
        $olderTerm = Term::create([
            'academic_session_id' => $olderSession->id,
            'name' => 'Chronologically Older Term',
            'start_date' => '2024-09-01',
            'end_date' => '2024-12-20',
        ]);
        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $newerTerm->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);
        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $olderTerm->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);
        $math = Subject::create(['name' => 'Published Math']);
        $science = Subject::create(['name' => 'Unpublished Science']);
        $mathClassSubject = ClassSubject::create(['class_id' => $class->id, 'subject_id' => $math->id]);
        $previousClass = SchoolClass::create(['name' => 'Student Previous Academic Class']);
        $previousMathClassSubject = ClassSubject::create(['class_id' => $previousClass->id, 'subject_id' => $math->id]);
        $scienceClassSubject = ClassSubject::create(['class_id' => $class->id, 'subject_id' => $science->id]);

        foreach ([
            ['term' => $newerTerm, 'subject' => $mathClassSubject, 'ca' => 24, 'exam' => 56],
            ['term' => $olderTerm, 'subject' => $previousMathClassSubject, 'ca' => 18, 'exam' => 42],
        ] as $result) {
            Result::create([
                'student_id' => $student->id,
                'class_subject_id' => $result['subject']->id,
                'term_id' => $result['term']->id,
                'ca_score' => $result['ca'],
                'exam_score' => $result['exam'],
            ]);
        }
        foreach ([
            ['term' => $newerTerm, 'date' => '2025-09-15', 'status' => 'present'],
            ['term' => $olderTerm, 'date' => '2024-09-15', 'status' => 'absent'],
        ] as $attendance) {
            Attendance::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'term_id' => $attendance['term']->id,
                'date' => $attendance['date'],
                'status' => $attendance['status'],
                'marked_by' => User::factory()->create(['role' => 'teacher'])->id,
            ]);
        }
        $unpublishedTerm = Term::create([
            'academic_session_id' => $newerSession->id,
            'name' => 'Unpublished Results Term',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-20',
        ]);
        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $unpublishedTerm->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);
        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $scienceClassSubject->id,
            'term_id' => $unpublishedTerm->id,
            'ca_score' => 30,
            'exam_score' => 70,
        ]);

        $response = $this->actingAs($studentUser)->get(route('student.dashboard'));

        $response->assertOk();
        $this->assertSame(70.0, $response->viewData('averageScore'));
        $this->assertSame(1, $response->viewData('subjectCount'));
        $this->assertSame(2, $response->viewData('totalRecords'));
        $this->assertSame(50.0, $response->viewData('attendanceRate'));
        $this->assertSame($newerTerm->id, $response->viewData('latestReportCard')->term_id);
        $this->assertSame([
            $olderTerm->name.' · '.$olderSession->name,
            $newerTerm->name.' · '.$newerSession->name,
        ], $response->viewData('resultTrend')['labels']);
        $this->assertSame([60.0, 80.0], $response->viewData('resultTrend')['averages']);
        $this->assertCount(2, $response->viewData('resultTrend')['rows']);
        $response->assertSee('Chronologically Newer Term');
        $response->assertDontSee('Unpublished Science');
    }

    public function test_student_dashboard_displays_a_real_zero_average_as_zero_not_as_missing(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'Student Zero Average Class']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-STUDENT-ZERO',
        ]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Zero Score Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
        ]);
        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);
        $subject = Subject::create(['name' => 'Zero Score Subject']);
        $classSubject = ClassSubject::create(['class_id' => $class->id, 'subject_id' => $subject->id]);
        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 0,
            'exam_score' => 0,
        ]);

        $response = $this->actingAs($studentUser)->get(route('student.dashboard'));

        $response->assertOk();
        $this->assertSame(0.0, $response->viewData('averageScore'));
        $response->assertSee('0%');
        $response->assertDontSee('No results');
        $this->assertCount(1, $response->viewData('resultTrend')['rows']);
        $response->assertSee('One published term is available; a trend cannot be inferred.');
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
            'status' => ReportCard::STATUS_PUBLISHED,
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
            'status' => ReportCard::STATUS_DRAFT,
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
            'status' => ReportCard::STATUS_DRAFT,
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
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }

    public function test_student_sees_timetable_entries_belonging_to_their_class(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-TT-1',
            'first_name' => 'Tim',
            'last_name' => 'Table',
        ]);

        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        Timetable::create([
            'class_subject_id' => $classSubject->id,
            'day' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '08:45:00',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/timetable');
        $response->assertOk();
        $response->assertSee('Mathematics');
    }

    public function test_student_does_not_see_timetable_entries_belonging_to_another_class(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-TT-2',
            'first_name' => 'Tim',
            'last_name' => 'Table',
        ]);

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $subject = Subject::create(['name' => 'Biology']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $subject->id,
        ]);

        Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'day' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '08:45:00',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/timetable');
        $response->assertOk();
        $response->assertDontSee('Biology');
    }

    public function test_today_classes_returns_real_database_timetable_for_current_day(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-TT-3',
            'first_name' => 'Tim',
            'last_name' => 'Table',
        ]);

        $subject = Subject::create(['name' => 'Chemistry']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-CHEM',
            'qualification' => 'M.Sc',
        ]);

        $today = now()->format('l');

        Timetable::create([
            'class_subject_id' => $classSubject->id,
            'teacher_id' => $teacher->id,
            'day' => $today,
            'start_time' => '09:00:00',
            'end_time' => '09:45:00',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/dashboard');
        $response->assertOk();
        $response->assertSee('Chemistry');
        $response->assertSee($teacherUser->name);
    }

    public function test_today_classes_does_not_return_entries_from_another_class(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-TT-4',
            'first_name' => 'Tim',
            'last_name' => 'Table',
        ]);

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $subject = Subject::create(['name' => 'Physics']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $subject->id,
        ]);

        $today = now()->format('l');

        Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'day' => $today,
            'start_time' => '09:00:00',
            'end_time' => '09:45:00',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get('/student/dashboard');
        $response->assertOk();
        $response->assertDontSee('Physics');
    }

    public function test_student_school_class_relationship_returns_correct_class(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM111',
            'first_name' => 'Test',
            'last_name' => 'Student',
        ]);

        $this->actingAs($studentUser);

        $student = $student->fresh();
        $this->assertInstanceOf(SchoolClass::class, $student->schoolClass);
        $this->assertEquals('JSS 1', $student->schoolClass->name);
        $this->assertEquals($class->id, $student->schoolClass->id);
    }

    public function test_student_without_profile_receives_403_on_dashboard(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentUser);

        $response = $this->get('/student/dashboard');

        $response->assertStatus(403);
    }

    public function test_student_without_profile_receives_403_on_attendance(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentUser);

        $response = $this->get('/student/attendance');

        $response->assertStatus(403);
    }

    public function test_student_without_profile_receives_403_on_fees(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentUser);

        $response = $this->get('/student/fees');

        $response->assertStatus(403);
    }

    public function test_student_without_profile_receives_403_on_timetable(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentUser);

        $response = $this->get('/student/timetable');

        $response->assertStatus(403);
    }

    public function test_student_without_profile_receives_403_on_profile(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentUser);

        $response = $this->get('/student/profile');

        $response->assertStatus(403);
    }

    public function test_student_without_profile_receives_403_on_report_cards(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentUser);

        $response = $this->get('/student/report-cards');

        $response->assertStatus(403);
    }
}
