<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\Period;
use App\Models\PeriodConfig;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\Timetable;
use App\Models\User;
use App\Services\TimetableGeneratorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected SchoolClass $class;

    protected Subject $subject;

    protected Teacher $teacher;

    protected Term $term;

    protected AcademicSession $session;

    protected ClassSubject $classSubject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);

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
            'end_date' => '2026-12-23',
            'is_current' => true,
        ]);

        $this->class = SchoolClass::create(['name' => 'JSS 1']);
        $this->subject = Subject::create(['name' => 'Mathematics']);
        $this->classSubject = ClassSubject::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'is_compulsory' => true,
        ]);

        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'name' => 'John Okafor',
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-2001',
            'qualification' => 'B.Ed',
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $this->teacher->id,
            'class_subject_id' => $this->classSubject->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_timetable_index(): void
    {
        $response = $this->get(route('admin.timetable.index'));

        $response->assertOk();
        $response->assertViewIs('admin.timetable.index');
        $response->assertViewHasAll(['classes', 'teachers', 'terms', 'sessions', 'timetable', 'currentTerm']);
    }

    public function test_admin_can_create_timetable_entry(): void
    {
        $response = $this->post(route('admin.timetable.store'), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $this->assertDatabaseHas('timetables', [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'term_id' => $this->term->id,
        ]);
    }

    public function test_timetable_requires_valid_time_range(): void
    {
        $response = $this->post(route('admin.timetable.store'), [
            'class_subject_id' => $this->classSubject->id,
            'day' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '08:00',
            'term_id' => $this->term->id,
        ]);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertDatabaseMissing('timetables', [
            'class_subject_id' => $this->classSubject->id,
            'day' => 'Monday',
        ]);
    }

    public function test_timetable_requires_valid_class_subject(): void
    {
        $response = $this->post(route('admin.timetable.store'), [
            'class_subject_id' => 999,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors(['class_subject_id']);
    }

    public function test_admin_can_filter_timetable_by_class(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $otherSubject = Subject::create(['name' => 'English']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $otherSubject->id,
        ]);

        Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Tuesday',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->get(route('admin.timetable.index', ['class_id' => $this->class->id]));
        $response->assertOk();

        $timetable = $response->viewData('timetable');
        $this->assertCount(1, $timetable);
        $this->assertEquals('Monday', $timetable->first()->day);
    }

    public function test_admin_can_filter_timetable_by_teacher(): void
    {
        $teacher2User = User::factory()->create(['role' => 'teacher', 'name' => 'Jane Doe']);
        $teacher2 = Teacher::create([
            'user_id' => $teacher2User->id,
            'employee_id' => 'T-2002',
        ]);

        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $teacher2->id,
            'day' => 'Monday',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->get(route('admin.timetable.index', ['teacher_id' => $this->teacher->id]));
        $response->assertOk();

        $timetable = $response->viewData('timetable');
        $this->assertCount(1, $timetable);
        $this->assertEquals($this->teacher->id, $timetable->first()->teacher_id);
    }

    public function test_admin_can_edit_timetable_entry(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->get(route('admin.timetable.edit', $timetable));
        $response->assertOk();
        $response->assertViewIs('admin.timetable.edit');
    }

    public function test_admin_can_update_timetable_entry(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->put(route('admin.timetable.update', $timetable), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Tuesday',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'term_id' => $this->term->id,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable->id,
            'day' => 'Tuesday',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
    }

    public function test_admin_can_delete_timetable_entry(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->delete(route('admin.timetable.destroy', $timetable));
        $response->assertRedirect(route('admin.timetable.index'));
        $this->assertDatabaseMissing('timetables', ['id' => $timetable->id]);
    }

    public function test_timetable_groups_by_day_correctly(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Tuesday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->get(route('admin.timetable.index'));
        $response->assertOk();

        $timetable = $response->viewData('timetable');
        $this->assertCount(2, $timetable);
        $days = $timetable->pluck('day')->sort();
        $this->assertEquals(['Monday', 'Tuesday'], $days->values()->all());
    }

    public function test_admin_timetable_requires_admin_role(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get(route('admin.timetable.index'));

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Period Configuration Tests
    |--------------------------------------------------------------------------
    */

    protected function getDefaultPeriods(): array
    {
        return [
            ['period_number' => 1, 'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '08:45', 'is_break' => false, 'sort_order' => 0],
            ['period_number' => 2, 'name' => 'Period 2', 'start_time' => '08:45', 'end_time' => '09:30', 'is_break' => false, 'sort_order' => 1],
            ['period_number' => 3, 'name' => 'Period 3', 'start_time' => '09:30', 'end_time' => '10:15', 'is_break' => false, 'sort_order' => 2],
            ['period_number' => 4, 'name' => 'Period 4', 'start_time' => '10:15', 'end_time' => '11:00', 'is_break' => false, 'sort_order' => 3],
            ['period_number' => 5, 'name' => 'Break', 'start_time' => '11:00', 'end_time' => '11:30', 'is_break' => true, 'sort_order' => 4],
            ['period_number' => 6, 'name' => 'Period 5', 'start_time' => '11:30', 'end_time' => '12:15', 'is_break' => false, 'sort_order' => 5],
            ['period_number' => 7, 'name' => 'Period 6', 'start_time' => '12:15', 'end_time' => '13:00', 'is_break' => false, 'sort_order' => 6],
            ['period_number' => 8, 'name' => 'Period 7', 'start_time' => '13:00', 'end_time' => '13:45', 'is_break' => false, 'sort_order' => 7],
            ['period_number' => 9, 'name' => 'Period 8', 'start_time' => '13:45', 'end_time' => '14:30', 'is_break' => false, 'sort_order' => 8],
        ];
    }

    public function test_admin_can_configure_periods_per_day(): void
    {
        $response = $this->post(route('admin.timetable.periods.store'), [
            'periods_per_day' => 6,
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'periods' => $this->getDefaultPeriods(),
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('period_configs', [
            'term_id' => $this->term->id,
            'periods_per_day' => 6,
            'start_day' => 'Monday',
            'end_day' => 'Friday',
        ]);
    }

    public function test_admin_can_configure_period_times(): void
    {
        $periods = [
            ['period_number' => 1, 'name' => 'Period 1', 'start_time' => '07:30', 'end_time' => '08:15', 'is_break' => false, 'sort_order' => 0],
            ['period_number' => 2, 'name' => 'Break', 'start_time' => '08:15', 'end_time' => '08:45', 'is_break' => true, 'sort_order' => 1],
            ['period_number' => 3, 'name' => 'Period 2', 'start_time' => '08:45', 'end_time' => '09:30', 'is_break' => false, 'sort_order' => 2],
        ];

        $response = $this->post(route('admin.timetable.periods.store'), [
            'periods_per_day' => 2,
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'periods' => $periods,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $periodConfig = PeriodConfig::where('term_id', $this->term->id)->first();
        $period1 = Period::where('period_config_id', $periodConfig->id)
            ->where('period_number', 1)
            ->first();
        $this->assertEquals('07:30', Carbon::parse($period1->start_time)->format('H:i'));
    }

    public function test_invalid_overlapping_period_times_rejected(): void
    {
        $periods = [
            ['period_number' => 1, 'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '09:00', 'is_break' => false, 'sort_order' => 0],
            ['period_number' => 2, 'name' => 'Period 2', 'start_time' => '08:30', 'end_time' => '09:30', 'is_break' => false, 'sort_order' => 1],
        ];

        $response = $this->post(route('admin.timetable.periods.store'), [
            'periods_per_day' => 2,
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'periods' => $periods,
        ]);

        $response->assertSessionHasErrors();
    }

    /*
    |--------------------------------------------------------------------------
    | Generator Tests
    |--------------------------------------------------------------------------
    */

    public function test_generator_reads_registered_class_subjects_only(): void
    {
        $otherSubject = Subject::create(['name' => 'Science']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $this->class->id,
            'subject_id' => $otherSubject->id,
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $this->teacher->id,
            'class_subject_id' => $otherClassSubject->id,
            'is_active' => true,
        ]);

        $generator = new TimetableGeneratorService(
            PeriodConfig::firstOrCreate(
                ['term_id' => $this->term->id],
                ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
            )
        );

        $this->ensureDefaultPeriods($generator);

        $result = $generator->generate();

        $allSubjects = $result['entries']->pluck('subject')->unique();
        $this->assertCount(2, $allSubjects);
        $this->assertTrue($allSubjects->contains('Mathematics'));
        $this->assertTrue($allSubjects->contains('Science'));
    }

    public function test_generator_uses_periods_per_week_from_class_subject(): void
    {
        $this->classSubject->update(['periods_per_week' => 3]);

        $generator = new TimetableGeneratorService(
            PeriodConfig::firstOrCreate(
                ['term_id' => $this->term->id],
                ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
            )
        );

        $this->ensureDefaultPeriods($generator);

        $result = $generator->generate();

        $entriesForSubject = $result['entries']->filter(fn ($e) => $e['subject'] === 'Mathematics');
        $this->assertGreaterThanOrEqual(1, $entriesForSubject->count());
    }

    public function test_generator_uses_teacher_class_subject_assignments(): void
    {
        $generator = new TimetableGeneratorService(
            PeriodConfig::firstOrCreate(
                ['term_id' => $this->term->id],
                ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
            )
        );

        $this->ensureDefaultPeriods($generator);

        $result = $generator->generate();

        $entriesWithTeacher = $result['entries']->where('has_teacher', true);
        $this->assertGreaterThan(0, $entriesWithTeacher->count());
    }

    public function test_generator_global_teacher_conflict_prevention(): void
    {
        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $this->subject->id,
            'periods_per_week' => 1,
        ]);

        $generator = new TimetableGeneratorService(
            PeriodConfig::firstOrCreate(
                ['term_id' => $this->term->id],
                ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
            )
        );

        $this->ensureDefaultPeriods($generator);

        $result = $generator->generate();

        $entriesForTeacher = $result['entries']->where('teacher_id', $this->teacher->id);
        $uniqueSlots = $entriesForTeacher->map(fn ($e) => $e['day'].'-'.$e['start_time'])->unique();

        $this->assertEquals($entriesForTeacher->count(), $uniqueSlots->count(),
            'Teacher should not have overlapping timetable entries.');
    }

    protected function ensureDefaultPeriods(TimetableGeneratorService $generator): void
    {
        $config = $generator->getPeriodConfig();
        if ($config->periods()->count() === 0) {
            $periods = [
                ['period_number' => 1, 'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '08:45', 'is_break' => false, 'sort_order' => 0],
                ['period_number' => 2, 'name' => 'Period 2', 'start_time' => '08:45', 'end_time' => '09:30', 'is_break' => false, 'sort_order' => 1],
                ['period_number' => 3, 'name' => 'Period 3', 'start_time' => '09:30', 'end_time' => '10:15', 'is_break' => false, 'sort_order' => 2],
                ['period_number' => 4, 'name' => 'Period 4', 'start_time' => '10:15', 'end_time' => '11:00', 'is_break' => false, 'sort_order' => 3],
                ['period_number' => 5, 'name' => 'Break', 'start_time' => '11:00', 'end_time' => '11:30', 'is_break' => true, 'sort_order' => 4],
                ['period_number' => 6, 'name' => 'Period 5', 'start_time' => '11:30', 'end_time' => '12:15', 'is_break' => false, 'sort_order' => 5],
                ['period_number' => 7, 'name' => 'Period 6', 'start_time' => '12:15', 'end_time' => '13:00', 'is_break' => false, 'sort_order' => 6],
                ['period_number' => 8, 'name' => 'Period 7', 'start_time' => '13:00', 'end_time' => '13:45', 'is_break' => false, 'sort_order' => 7],
                ['period_number' => 9, 'name' => 'Period 8', 'start_time' => '13:45', 'end_time' => '14:30', 'is_break' => false, 'sort_order' => 8],
            ];

            foreach ($periods as $p) {
                $config->periods()->create($p);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generation & Preview Tests
    |--------------------------------------------------------------------------
    */

    public function test_generation_creates_preview(): void
    {
        $this->classSubject->update(['periods_per_week' => 2]);

        $response = $this->post(route('admin.timetable.generate'));

        $response->assertRedirect(route('admin.timetable.index'));
        $response->assertSessionHas('status');
    }

    public function test_generation_shows_warnings_for_missing_teacher(): void
    {
        $this->classSubject->update(['periods_per_week' => 2]);
        $this->classSubject->teacherAssignments()->delete();

        $generator = new TimetableGeneratorService(
            PeriodConfig::firstOrCreate(
                ['term_id' => $this->term->id],
                ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
            )
        );

        $this->ensureDefaultPeriods($generator);

        $result = $generator->generate();

        $this->assertTrue($result['warnings']->isNotEmpty());
    }

    public function test_confirm_save_persists_generated_timetable(): void
    {
        $this->classSubject->update(['periods_per_week' => 2]);

        $config = PeriodConfig::firstOrCreate(
            ['term_id' => $this->term->id],
            ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
        );

        $this->ensureDefaultPeriods(new TimetableGeneratorService($config));

        $generator = new TimetableGeneratorService($config);
        $result = $generator->generate();

        session()->put('timetable_preview', $result);

        $response = $this->post(route('admin.timetable.confirm-generate'));

        $response->assertRedirect(route('admin.timetable.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('timetables', [
            'class_subject_id' => $this->classSubject->id,
            'day' => 'Monday',
        ]);
    }

    public function test_generated_timetable_can_be_filtered_by_class(): void
    {
        $this->assertDatabaseCount('timetables', 0);

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $otherSubject = Subject::create(['name' => 'English']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $otherSubject->id,
            'periods_per_week' => 1,
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $this->teacher->id,
            'class_subject_id' => $otherClassSubject->id,
            'is_active' => true,
        ]);

        $this->classSubject->update(['periods_per_week' => 1]);

        $config = PeriodConfig::firstOrCreate(
            ['term_id' => $this->term->id],
            ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
        );

        $generator = new TimetableGeneratorService($config);
        $this->ensureDefaultPeriods($generator);
        $result = $generator->generate();
        $generator->saveGenerated($result['entries']->toArray());

        $response = $this->get(route('admin.timetable.index', ['class_id' => $this->class->id]));
        $response->assertOk();
        $timetable = $response->viewData('timetable');
        $this->assertCount(1, $timetable);
        $this->assertEquals($this->classSubject->id, $timetable->first()->class_subject_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Manual Edit Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_move_lesson_to_another_period(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->put(route('admin.timetable.update', $timetable), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '10:15',
            'end_time' => '11:00',
            'term_id' => $this->term->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable->id,
        ]);
        $updated = Timetable::find($timetable->id);
        $this->assertEquals('10:15', Carbon::parse($updated->start_time)->format('H:i'));
    }

    public function test_admin_can_move_lesson_to_another_day(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->put(route('admin.timetable.update', $timetable), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Wednesday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable->id,
            'day' => 'Wednesday',
        ]);
    }

    public function test_invalid_manual_move_rejected(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $timetable2 = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => null,
            'day' => 'Monday',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->put(route('admin.timetable.update', $timetable2), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $updated = Timetable::find($timetable2->id);
        $this->assertEquals('10:00', Carbon::parse($updated->start_time)->format('H:i'));
        $this->assertNull($updated->teacher_id);
    }

    public function test_admin_can_add_manual_entry_with_valid_assignment(): void
    {
        $response = $this->post(route('admin.timetable.store'), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Friday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $this->assertDatabaseHas('timetables', [
            'class_subject_id' => $this->classSubject->id,
            'day' => 'Friday',
            'is_manual' => true,
        ]);
    }

    public function test_filters_work_after_generation(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->get(route('admin.timetable.index', ['day' => 'Monday']));
        $response->assertOk();
        $timetable = $response->viewData('timetable');
        $this->assertCount(1, $timetable);

        $response = $this->get(route('admin.timetable.index', ['day' => 'Tuesday']));
        $response->assertOk();
        $timetable = $response->viewData('timetable');
        $this->assertCount(0, $timetable);
    }

    /*
    |--------------------------------------------------------------------------
    | Regeneration & Locking Tests
    |--------------------------------------------------------------------------
    */

    public function test_regenerate_overwrites_generated_entries(): void
    {
        $this->classSubject->update(['periods_per_week' => 1]);

        $config = PeriodConfig::firstOrCreate(
            ['term_id' => $this->term->id],
            ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
        );

        $generator = new TimetableGeneratorService($config);
        $this->ensureDefaultPeriods($generator);
        $result = $generator->generate();
        $generator->saveGenerated($result['entries']->toArray());

        $firstCount = Timetable::where('term_id', $this->term->id)->count();
        $this->assertGreaterThan(0, $firstCount);

        Timetable::where('term_id', $this->term->id)->first()->update([
            'start_time' => '10:00',
            'is_locked' => true,
        ]);

        $result2 = $generator->generate();
        $generator->saveGenerated($result2['entries']->toArray());

        $lockedEntry = Timetable::where('term_id', $this->term->id)
            ->where('is_locked', true)
            ->first();
        $this->assertNotNull($lockedEntry);
        $this->assertEquals('10:00', Carbon::parse($lockedEntry->start_time)->format('H:i'));
    }

    public function test_locked_entries_preserved_on_regeneration(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'term_id' => $this->term->id,
            'is_manual' => true,
            'is_locked' => true,
        ]);

        $config = PeriodConfig::firstOrCreate(
            ['term_id' => $this->term->id],
            ['academic_session_id' => $this->session->id, 'periods_per_day' => 8, 'start_day' => 'Monday', 'end_day' => 'Friday']
        );

        $generator = new TimetableGeneratorService($config);
        $this->ensureDefaultPeriods($generator);

        $lockedEntry = Timetable::where('is_locked', true)->first();
        $this->assertNotNull($lockedEntry);
        $this->assertEquals('14:00', Carbon::parse($lockedEntry->start_time)->format('H:i'));

        $generator->saveGenerated([]);

        $stillLocked = Timetable::where('is_locked', true)->first();
        $this->assertNotNull($stillLocked);
        $this->assertEquals('14:00', Carbon::parse($stillLocked->start_time)->format('H:i'));
    }

    /*
    |--------------------------------------------------------------------------
    | Student/Teacher/Parent Views Tests
    |--------------------------------------------------------------------------
    */

    public function test_student_timetable_reflects_admin_changes(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $this->class->id,
            'admission_no' => 'STU001',
        ]);

        $this->actingAs($studentUser);
        $response = $this->get('/student/timetable');
        $response->assertOk();
        $response->assertSee('Mathematics');
        $response->assertSee('Monday');
    }

    public function test_teacher_timetable_reflects_admin_changes(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $teacherUser = User::find($this->teacher->user_id);
        $this->actingAs($teacherUser);
        $response = $this->get('/teacher/timetable');
        $response->assertOk();
        $response->assertSee('Mathematics');
    }

    public function test_admin_can_filter_timetable_by_subject(): void
    {
        $otherSubject = Subject::create(['name' => 'Science']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $this->class->id,
            'subject_id' => $otherSubject->id,
            'periods_per_week' => 1,
        ]);

        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Tuesday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $mathSubjectId = $this->subject->id;
        $response = $this->get(route('admin.timetable.index', ['subject_id' => $mathSubjectId]));
        $response->assertOk();
        $timetable = $response->viewData('timetable');
        $this->assertCount(1, $timetable);
        $this->assertEquals('Mathematics', $timetable->first()->classSubject->subject->name);
    }
}
