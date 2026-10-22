<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Services\AdminDashboardAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SyntheticAttendanceDemoCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private AcademicSession $session;

    private Term $term;

    private SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
        $this->term = Term::create([
            'academic_session_id' => $this->session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-18',
            'is_current' => true,
        ]);
        $this->class = SchoolClass::create(['name' => 'JSS 1']);
    }

    public function test_default_period_previews_weekdays_and_never_writes_without_confirmation(): void
    {
        $this->createStudent($this->class, '2026-08-01');

        $this->artisan('attendance:seed-synthetic-demo')
            ->expectsOutputToContain('38 weekdays')
            ->expectsOutputToContain('Expected attendance records: 38.')
            ->expectsOutputToContain('Dry run complete. No attendance records were written.')
            ->assertExitCode(0);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_preview_excludes_weekends_inactive_students_and_pre_admission_dates(): void
    {
        $this->createStudent($this->class, '2026-09-07');
        $this->createStudent($this->class, '2026-08-01', 'inactive');
        $this->createStudent($this->class, '2026-08-01', 'active', false);

        $this->artisan('attendance:seed-synthetic-demo', [
            '--from' => '2026-09-05',
            '--through' => '2026-09-08',
        ])->expectsOutputToContain('2 weekdays')
            ->expectsOutputToContain('Expected attendance records: 2.')
            ->assertExitCode(0);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_confirmed_execution_uses_only_supported_deterministic_statuses_and_reruns_safely(): void
    {
        $student = $this->createStudent($this->class, '2026-08-01');
        $options = [
            '--from' => '2026-09-04',
            '--through' => '2026-09-08',
            '--marked-by' => (string) $this->admin->id,
            '--assume-current-roster' => true,
            '--confirm-synthetic' => true,
        ];

        $firstExitCode = Artisan::call('attendance:seed-synthetic-demo', $options);
        $firstRows = Attendance::query()->orderBy('date')->get();

        $this->assertSame(0, $firstExitCode);
        $this->assertCount(3, $firstRows);
        $this->assertSame(['2026-09-04', '2026-09-07', '2026-09-08'], $firstRows->map(fn (Attendance $row) => $row->date->toDateString())->all());
        $this->assertSame([$student->id], $firstRows->pluck('student_id')->unique()->all());
        $this->assertSame([$this->class->id], $firstRows->pluck('class_id')->unique()->all());
        $this->assertSame([$this->term->id], $firstRows->pluck('term_id')->unique()->all());
        $this->assertSame([$this->admin->id], $firstRows->pluck('marked_by')->unique()->all());
        $this->assertTrue($firstRows->every(fn (Attendance $row) => $row->is_synthetic));
        $this->assertTrue($firstRows->every(fn (Attendance $row) => in_array($row->status, ['present', 'absent', 'late'], true)));
        $this->assertSame(0, Attendance::real()->count());

        $firstValues = $firstRows->map(fn (Attendance $row) => [$row->date->toDateString(), $row->status])->all();
        $secondExitCode = Artisan::call('attendance:seed-synthetic-demo', $options);
        $secondRows = Attendance::query()->orderBy('date')->get();

        $this->assertSame(0, $secondExitCode);
        $this->assertSame($firstValues, $secondRows->map(fn (Attendance $row) => [$row->date->toDateString(), $row->status])->all());
        $this->assertDatabaseCount('attendances', 3);

        $analytics = app(AdminDashboardAnalytics::class)->attendance(
            $this->term,
            collect([$this->class]),
            CarbonImmutable::parse('2026-09-07'),
            $this->class->id
        );
        $this->assertSame(3, $analytics['syntheticCount']);
        $this->assertSame(3, collect($analytics['trend']['rows'])->sum(fn (array $row) => collect($row)->except('label')->sum()));
        $this->assertTrue($analytics['classComparison']['hasRecords']);
    }

    public function test_existing_real_records_are_preserved_and_counted_as_skips(): void
    {
        $student = $this->createStudent($this->class, '2026-08-01');
        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $this->class->id,
            'term_id' => $this->term->id,
            'date' => '2026-09-07',
            'status' => 'absent',
            'marked_by' => $this->admin->id,
        ]);

        Artisan::call('attendance:seed-synthetic-demo', [
            '--from' => '2026-09-07',
            '--through' => '2026-09-07',
            '--marked-by' => (string) $this->admin->id,
            '--assume-current-roster' => true,
            '--confirm-synthetic' => true,
        ]);

        $this->assertSame(1, Attendance::count());
        $saved = Attendance::query()->firstOrFail();
        $this->assertSame($student->id, $saved->student_id);
        $this->assertSame('absent', $saved->status);
        $this->assertFalse($saved->is_synthetic);
    }

    public function test_insert_requires_explicit_current_roster_acknowledgement(): void
    {
        $this->createStudent($this->class, '2026-08-01');

        $this->artisan('attendance:seed-synthetic-demo', [
            '--from' => '2026-09-07',
            '--through' => '2026-09-07',
            '--marked-by' => (string) $this->admin->id,
            '--confirm-synthetic' => true,
        ])->expectsOutputToContain('requires --assume-current-roster')
            ->assertExitCode(1);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_period_must_map_to_exactly_one_term_and_current_session(): void
    {
        $this->createStudent($this->class, '2026-08-01');
        $this->term->update(['end_date' => '2026-09-04']);

        $this->artisan('attendance:seed-synthetic-demo', [
            '--from' => '2026-09-04',
            '--through' => '2026-09-07',
        ])->expectsOutputToContain('maps to 0 configured terms')
            ->assertExitCode(1);
        $this->assertDatabaseCount('attendances', 0);

        $this->term->update(['end_date' => '2026-12-18']);
        $this->session->update(['is_current' => false]);

        $this->artisan('attendance:seed-synthetic-demo', [
            '--from' => '2026-09-04',
            '--through' => '2026-09-07',
        ])->expectsOutputToContain('exactly one configured current academic session')
            ->assertExitCode(1);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_existing_key_in_a_different_class_stops_insertion(): void
    {
        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $student = $this->createStudent($this->class, '2026-08-01');
        $this->assertSame(1, Student::query()
            ->where('status', 'active')
            ->whereNotNull('class_id')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->count());
        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $otherClass->id,
            'term_id' => $this->term->id,
            'date' => '2026-09-07',
            'status' => 'present',
            'marked_by' => $this->admin->id,
            'is_synthetic' => false,
        ]);

        $this->artisan('attendance:seed-synthetic-demo', [
            '--from' => '2026-09-07',
            '--through' => '2026-09-07',
            '--marked-by' => (string) $this->admin->id,
            '--assume-current-roster' => true,
            '--confirm-synthetic' => true,
        ])->expectsOutputToContain('different class')
            ->assertExitCode(1);
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['id' => 1, 'class_id' => $otherClass->id, 'status' => 'present']);
    }

    private function createStudent(
        SchoolClass $class,
        ?string $admissionDate,
        string $status = 'active',
        bool $activeAccount = true
    ): Student
    {
        return Student::create([
            'user_id' => User::factory()->create([
                'role' => 'student',
                'is_active' => $activeAccount,
            ])->id,
            'admission_no' => 'DEMO-'.uniqid(),
            'class_id' => $class->id,
            'admission_date' => $admissionDate,
            'status' => $status,
        ]);
    }
}
