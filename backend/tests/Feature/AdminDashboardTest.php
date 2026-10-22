<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_sections_remain_inside_the_main_content_container_after_kpi_rendering(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $document = new \DOMDocument();
        $document->loadHTML($response->getContent(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $dashboard = $xpath->query(
            '//*[@id="main-content"]/div[contains(concat(" ", normalize-space(@class), " "), " max-w-[1600px] ")]'
        )->item(0);

        $this->assertNotNull($dashboard, 'The dashboard root should remain inside the scrollable main content.');

        foreach (['attendance-heading', 'finance-title', 'results-title', 'announcements-title', 'activity-title'] as $headingId) {
            $this->assertSame(
                1,
                $xpath->query('descendant::*[@id="'.$headingId.'"]', $dashboard)->length,
                "The {$headingId} section should remain inside the dashboard root."
            );
        }
    }

    public function test_admin_dashboard_scopes_metrics_and_attendance_data_without_duplicate_join_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $term = $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);
        $pastTerm = $this->createTerm('Past term', now()->subYear(), now()->subMonths(11));
        $classA = SchoolClass::create(['name' => 'Class A']);
        $classB = SchoolClass::create(['name' => 'Class B']);
        $classC = SchoolClass::create(['name' => 'Class C']);

        $activeStudentA = $this->createStudent($classA, 'active', true);
        $secondActiveStudentA = $this->createStudent($classA, 'active', true);
        $inactiveStudentA = $this->createStudent($classA, 'inactive', true);
        $activeStudentB = $this->createStudent($classB, 'active', true);
        $inactiveAccountStudent = $this->createStudent($classB, 'active', false);
        $activeTeacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        $inactiveTeacher = User::factory()->create(['role' => 'teacher', 'is_active' => false]);
        Teacher::create(['user_id' => $activeTeacher->id, 'employee_id' => 'T-DASH-ACTIVE']);
        Teacher::create(['user_id' => $inactiveTeacher->id, 'employee_id' => 'T-DASH-INACTIVE']);

        $today = CarbonImmutable::today();
        $yesterday = $today->subDay();
        Attendance::create([
            'student_id' => $activeStudentA->id,
            'class_id' => $classA->id,
            'term_id' => $term->id,
            'date' => $today->toDateString(),
            'status' => 'present',
            'marked_by' => $admin->id,
        ]);
        Attendance::create([
            'student_id' => $inactiveStudentA->id,
            'class_id' => $classA->id,
            'term_id' => $term->id,
            'date' => $today->toDateString(),
            'status' => 'absent',
            'marked_by' => $admin->id,
        ]);
        Attendance::create([
            'student_id' => $activeStudentB->id,
            'class_id' => $classB->id,
            'term_id' => $term->id,
            'date' => $yesterday->toDateString(),
            'status' => 'late',
            'marked_by' => $admin->id,
        ]);
        Attendance::create([
            'student_id' => $activeStudentB->id,
            'class_id' => $classB->id,
            'term_id' => $pastTerm->id,
            'date' => $today->toDateString(),
            'status' => 'absent',
            'marked_by' => $admin->id,
        ]);

        $subject = Subject::create(['name' => 'Dashboard subject']);
        $classSubject = \App\Models\ClassSubject::create([
            'class_id' => $classA->id,
            'subject_id' => $subject->id,
        ]);
        Result::create([
            'student_id' => $activeStudentA->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 20,
            'exam_score' => 60,
            'is_locked' => true,
        ]);

        foreach (range(1, 4) as $index) {
            $announcement = Announcement::create([
                'title' => 'Dashboard announcement '.$index,
                'body' => 'Announcement body '.$index,
                'created_by' => $admin->id,
            ]);
            $announcement->forceFill(['created_at' => now()->addSeconds($index)])->save();

            $activity = AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'Dashboard activity '.$index,
                'old_value' => ['private' => 'sensitive audit payload'],
            ]);
            $activity->forceFill(['created_at' => now()->addSeconds($index)])->save();
        }

        $currentFeeType = FeeType::create([
            'name' => 'Current dashboard fee',
            'amount' => 100,
            'term_id' => $term->id,
        ]);
        $pastFeeType = FeeType::create([
            'name' => 'Past dashboard fee',
            'amount' => 100,
            'term_id' => $pastTerm->id,
        ]);
        $currentFee = StudentFee::create([
            'student_id' => $activeStudentA->id,
            'fee_type_id' => $currentFeeType->id,
            'term_id' => $term->id,
            'amount_expected' => 100,
        ]);
        StudentFee::create([
            'student_id' => $activeStudentA->id,
            'fee_type_id' => $pastFeeType->id,
            'term_id' => $pastTerm->id,
            'amount_expected' => 100,
        ]);
        Payment::create([
            'student_fee_id' => $currentFee->id,
            'receipt_number' => 'RCPT-DASH-1',
            'amount_paid' => 50,
            'payment_method' => 'cash',
            'payment_date' => $today->toDateString(),
            'recorded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'class_id' => $classA->id,
            'date' => $today->toDateString(),
        ]));

        $response->assertOk();
        $this->assertSame(3, $response->viewData('activeStudents'));
        $this->assertSame(1, $response->viewData('activeTeachers'));
        $this->assertSame(3, $response->viewData('totalClasses'));
        $this->assertSame([
            'recorded' => 1,
            'locked' => 1,
            'unlocked' => 0,
        ], $response->viewData('resultMetrics'));
        $this->assertSame(['Present' => 1], collect($response->viewData('attendance')['today']['rows'])->mapWithKeys(fn ($row) => [$row['label'] => $row['count']])->all());
        $this->assertSame(1, $response->viewData('attendance')['today']['covered']);
        $this->assertSame(3, $response->viewData('attendance')['today']['roster']);
        $this->assertSame(33.3, $response->viewData('attendance')['today']['coverage']);
        $this->assertSame(2, $response->viewData('attendance')['trend']['labels'] ? count($response->viewData('attendance')['trend']['labels']) : 0);

        $comparison = collect($response->viewData('attendance')['classComparison']['rows'])->keyBy('label');
        $this->assertSame(2, $comparison['Class A']['records']);
        $this->assertTrue($comparison['Class C']['hasRecords'] === false);
        $this->assertSame(0, $comparison['Class C']['records']);

        $detail = $response->viewData('attendance')['classDetail'];
        $this->assertSame(2, $detail['roster']);
        $this->assertSame(1, $detail['covered']);
        $this->assertSame(50.0, $detail['coverage']);
        $this->assertSame([
            ['label' => 'Present', 'count' => 1],
            ['label' => 'Absent', 'count' => 1],
        ], $detail['rows']);

        $enrollment = collect($response->viewData('enrollment'))->keyBy('label');
        $this->assertSame(2, $enrollment['Class A']['count']);
        $this->assertSame(1, $enrollment['Class B']['count']);
        $this->assertSame(0, $enrollment['Class C']['count']);
        $this->assertSame(200.0, $response->viewData('finance')['expected']);
        $this->assertSame(50.0, $response->viewData('finance')['collected']);

        $response->assertSee($today->format('j F Y').' · '.$term->name);
        $response->assertSee('Register completion 50%');
        $response->assertSee('Comparison is school-wide for this date; class selection filters detail only.');
        $response->assertDontSee('Pending submissions');
        $response->assertSee('All recorded terms.')
            ->assertSee('₦50.00')
            ->assertSee('₦150.00')
            ->assertSee('₦200.00')
            ->assertSee(route('admin.finance'));
        $response->assertSee('Result Records')
            ->assertSee('Dashboard announcement 4')
            ->assertSee('Dashboard announcement 3')
            ->assertSee('Dashboard announcement 2')
            ->assertDontSee('Dashboard announcement 1')
            ->assertSee('Dashboard Activity 4')
            ->assertSee('Dashboard Activity 3')
            ->assertSee('Dashboard Activity 2')
            ->assertDontSee('Dashboard Activity 1')
            ->assertDontSee('sensitive audit payload');
    }

    public function test_enrollment_chart_height_grows_with_the_number_of_classes_without_stretching_finance_card(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);

        foreach (range(1, 7) as $index) {
            SchoolClass::create(['name' => 'Enrollment class '.$index]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]', false)
            ->assertSee('height: 336px;', false)
            ->assertSee('All recorded terms.')
            ->assertSee(route('admin.finance'));
    }

    public function test_admin_dashboard_shows_no_records_instead_of_treating_an_empty_class_register_as_zero_absences(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $term = $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);
        $class = SchoolClass::create(['name' => 'Empty register class']);
        $this->createStudent($class);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'class_id' => $class->id,
            'date' => CarbonImmutable::today()->toDateString(),
        ]));

        $response->assertOk();
        $attendance = $response->viewData('attendance');
        $this->assertFalse($attendance['classDetail']['hasRecords']);
        $this->assertNull($attendance['classDetail']['coverage']);
        $this->assertFalse($attendance['classComparison']['hasRecords']);
        $this->assertSame([], $attendance['trend']['labels']);
        $this->assertSame(1, $attendance['today']['roster']);
        $this->assertSame(0, $attendance['today']['covered']);
        $this->assertNull($attendance['today']['coverage']);
        $response->assertSee('0 of 1 students recorded; completion, not presence.');
        $response->assertDontSee('Class register detail');
        $response->assertSee('No records. Missing entries are not absences.');
        $response->assertDontSee('Absent records');
    }

    public function test_class_register_detail_is_hidden_until_a_class_is_selected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertDontSee('Class register detail')
            ->assertDontSee('Choose a class and date above to inspect its register.');
    }

    public function test_admin_dashboard_requires_a_configured_current_term_for_term_scoped_analytics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $class = SchoolClass::create(['name' => 'No current term class']);
        $this->createStudent($class);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertNull($response->viewData('attendance'));
        $this->assertSame(1, $response->viewData('activeStudents'));
        $response->assertSee('Current term not configured.');
        $response->assertSee('Unavailable');
    }

    public function test_bottom_dashboard_cards_show_compact_empty_states_and_zero_result_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Result Records')
            ->assertSee('No published announcements.')
            ->assertSee(route('admin.announcements.create'))
            ->assertSee('No recent activity recorded.')
            ->assertSee(route('admin.audit-logs.index'));
        $this->assertSame([
            'recorded' => 0,
            'locked' => 0,
            'unlocked' => 0,
        ], $response->viewData('resultMetrics'));
    }

    public function test_dashboard_recent_activity_uses_friendly_action_and_hides_technical_metadata(): void
    {
        $admin = User::factory()->create(['name' => 'Principal Debowale', 'role' => 'admin']);
        $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'user.login',
            'target_model' => User::class,
            'target_id' => $admin->id,
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Logged in')
            ->assertSee('Principal Debowale')
            ->assertDontSee('user.login')
            ->assertDontSee('127.0.0.1');
    }

    public function test_admin_dashboard_includes_synthetic_attendance_with_an_explicit_warning(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $term = $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);
        $class = SchoolClass::create(['name' => 'Synthetic data class']);
        $student = $this->createStudent($class);

        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $term->id,
            'date' => CarbonImmutable::today()->toDateString(),
            'status' => 'present',
            'marked_by' => $admin->id,
            'is_synthetic' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Welcome back, '.$admin->name)
            ->assertSee('Synthetic records included: 1 · simulated, not genuine.');
        $this->assertSame(1, $response->viewData('attendance')['syntheticCount']);
        $this->assertSame(1, collect($response->viewData('attendance')['trend']['rows'])->sum(
            fn (array $row) => collect($row)->except('label')->sum()
        ));
    }

    public function test_complete_register_coverage_can_include_absent_students_without_becoming_an_attendance_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $term = $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);
        $class = SchoolClass::create(['name' => 'Complete register class']);
        $otherClass = SchoolClass::create(['name' => 'School-wide comparison class']);
        $presentStudent = $this->createStudent($class);
        $absentStudent = $this->createStudent($class);
        $otherStudent = $this->createStudent($otherClass);

        foreach ([
            [$presentStudent, 'present'],
            [$absentStudent, 'absent'],
            [$otherStudent, 'late'],
        ] as [$student, $status]) {
            Attendance::create([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'term_id' => $term->id,
                'date' => CarbonImmutable::today()->toDateString(),
                'status' => $status,
                'marked_by' => $admin->id,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'class_id' => $class->id,
            'date' => CarbonImmutable::today()->toDateString(),
        ]));

        $response->assertOk()
            ->assertSee('100%')
            ->assertSee('3 of 3 students recorded; completion, not presence.')
            ->assertSee('Absent records');

        $attendance = $response->viewData('attendance');
        $this->assertSame(100.0, $attendance['today']['coverage']);
        $this->assertSame(3, $attendance['today']['covered']);
        $this->assertSame(3, $attendance['today']['roster']);
        $this->assertSame([
            ['label' => 'Present', 'count' => 1],
            ['label' => 'Absent', 'count' => 1],
        ], $attendance['classDetail']['rows']);
        $comparison = collect($attendance['classComparison']['rows'])->keyBy('label');
        $this->assertSame(1, $comparison['School-wide comparison class']['Late']);
    }

    public function test_admin_dashboard_rejects_unknown_class_and_dates_outside_current_term(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $term = $this->createTerm('Current term', now()->subMonth(), now()->addMonth(), true);
        $class = SchoolClass::create(['name' => 'Validated class']);

        $unknownClassResponse = $this->actingAs($admin)->get(route('admin.dashboard', [
            'class_id' => 999999,
        ]));
        $unknownClassResponse->assertSessionHasErrors('class_id');

        $outOfTermResponse = $this->actingAs($admin)->get(route('admin.dashboard', [
            'class_id' => $class->id,
            'date' => $term->start_date->subDay()->toDateString(),
        ]));
        $outOfTermResponse->assertSessionHasErrors('date');
    }

    public function test_non_admin_cannot_view_admin_dashboard_analytics(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    private function createTerm(string $name, $startDate, $endDate, bool $current = false): Term
    {
        $session = AcademicSession::create([
            'name' => $name.' session',
            'start_date' => CarbonImmutable::parse($startDate)->toDateString(),
            'end_date' => CarbonImmutable::parse($endDate)->toDateString(),
            'is_current' => $current,
        ]);

        return Term::create([
            'academic_session_id' => $session->id,
            'name' => $name,
            'start_date' => CarbonImmutable::parse($startDate)->toDateString(),
            'end_date' => CarbonImmutable::parse($endDate)->toDateString(),
            'is_current' => $current,
        ]);
    }

    private function createStudent(SchoolClass $class, string $status = 'active', bool $userActive = true): Student
    {
        return Student::create([
            'user_id' => User::factory()->create([
                'role' => 'student',
                'is_active' => $userActive,
            ])->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-DASH-'.uniqid(),
            'status' => $status,
        ]);
    }
}
