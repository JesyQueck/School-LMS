<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\PeriodConfig;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\Timetable;
use App\Models\User;
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
    | Timetable Configuration Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_timetable_config(): void
    {
        $response = $this->get(route('admin.timetable.config'));
        $response->assertOk();
        $response->assertViewIs('admin.timetable.config');
    }

    public function test_admin_can_save_timetable_configuration(): void
    {
        $periods = [
            ['period_number' => 1, 'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '08:40', 'is_break' => '0', 'sort_order' => 0],
            ['period_number' => 2, 'name' => 'Period 2', 'start_time' => '08:40', 'end_time' => '09:20', 'is_break' => '0', 'sort_order' => 1],
            ['period_number' => 3, 'name' => 'Break', 'start_time' => '09:20', 'end_time' => '09:40', 'is_break' => '1', 'sort_order' => 2],
            ['period_number' => 4, 'name' => 'Period 3', 'start_time' => '09:40', 'end_time' => '10:20', 'is_break' => '0', 'sort_order' => 3],
        ];

        $response = $this->post(route('admin.timetable.config.save'), [
            'periods_per_day' => 4,
            'start_day' => 'Monday',
            'end_day' => 'Friday',
            'periods' => $periods,
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('period_configs', [
            'term_id' => $this->term->id,
            'periods_per_day' => 4,
            'start_day' => 'Monday',
            'end_day' => 'Friday',
        ]);
        $this->assertDatabaseHas('periods', [
            'period_config_id' => PeriodConfig::where('term_id', $this->term->id)->first()->id,
            'period_number' => 1,
            'name' => 'Period 1',
            'is_break' => false,
        ]);
        $this->assertDatabaseHas('periods', [
            'period_config_id' => PeriodConfig::where('term_id', $this->term->id)->first()->id,
            'period_number' => 3,
            'name' => 'Break',
            'is_break' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cell Management Tests (via AJAX)
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_cell_via_ajax(): void
    {
        $response = $this->post(route('timetable.cell.store'), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
            'academic_session_id' => $this->session->id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'message', 'entry']);

        $this->assertDatabaseHas('timetables', [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);
    }

    public function test_admin_can_update_cell_via_ajax(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->put(route('timetable.cell.update', $timetable->id), [
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Tuesday',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'term_id' => $this->term->id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('timetables', [
            'id' => $timetable->id,
            'day' => 'Tuesday',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
    }

    public function test_cell_create_conflict_is_rejected(): void
    {
        Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('timetable.cell.store'), [
            'class_subject_id' => $this->classSubject->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(409)
            ->assertJson(['success' => false]);
    }

    public function test_admin_can_delete_cell_via_ajax(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->delete(route('timetable.cell.destroy', $timetable->id), [], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('timetables', ['id' => $timetable->id]);
    }

    public function test_admin_can_filter_timetable_by_subject(): void
    {
        $otherSubject = Subject::create(['name' => 'Science']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $this->class->id,
            'subject_id' => $otherSubject->id,
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

    public function test_timetable_move_entry(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.move', $timetable), [
            'day' => 'Tuesday',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response->assertRedirect(route('admin.timetable.index'));
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable->id,
            'day' => 'Tuesday',
            'start_time' => '08:00',
        ]);
    }

    public function test_timetable_move_entry_validation(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.move', $timetable), [
            'day' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '08:00',
        ]);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable->id,
            'day' => 'Monday',
            'start_time' => '08:00',
        ]);
    }

    public function test_timetable_move_entry_conflict(): void
    {
        $timetable1 = Timetable::create([
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

        $timetable2 = Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.move', $timetable1), [
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable1->id,
            'day' => 'Monday',
            'start_time' => '08:00',
        ]);
    }

    public function test_timetable_move_entry_ajax_response(): void
    {
        $timetable = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '08:40',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.move', $timetable), [
            'day' => 'Tuesday',
            'start_time' => '10:10',
            'end_time' => '10:50',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'message']);

        $this->assertDatabaseHas('timetables', [
            'id' => $timetable->id,
            'day' => 'Tuesday',
            'start_time' => '10:10',
        ]);
    }

    public function test_timetable_move_entry_conflict_ajax_response(): void
    {
        $timetable1 = Timetable::create([
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

        $timetable2 = Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.move', $timetable1), [
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(409)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'message', 'errors']);

        $this->assertDatabaseHas('timetables', [
            'id' => $timetable1->id,
            'day' => 'Monday',
            'start_time' => '08:00',
        ]);
    }

    public function test_timetable_move_entry_swap_same_teacher(): void
    {
        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $otherSubject = Subject::create(['name' => 'English']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $otherSubject->id,
        ]);

        $timetable1 = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '08:40',
            'term_id' => $this->term->id,
        ]);

        $timetable2 = Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Tuesday',
            'start_time' => '10:10',
            'end_time' => '10:50',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.swap'), [
            'entry_a_id' => $timetable1->id,
            'entry_b_id' => $timetable2->id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('timetables', [
            'id' => $timetable1->id,
            'day' => 'Tuesday',
            'start_time' => '10:10',
            'end_time' => '10:50',
        ]);
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable2->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '08:40',
        ]);
    }

    public function test_timetable_swap_is_atomic_on_conflict(): void
    {
        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $otherSubject = Subject::create(['name' => 'English']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $otherSubject->id,
        ]);

        $timetable1 = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '08:40',
            'term_id' => $this->term->id,
        ]);

        $timetable3 = Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '08:40',
            'term_id' => $this->term->id,
        ]);

        $timetable2 = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Tuesday',
            'start_time' => '10:10',
            'end_time' => '10:50',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.swap'), [
            'entry_a_id' => $timetable1->id,
            'entry_b_id' => $timetable2->id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(409)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'message', 'errors']);

        $this->assertDatabaseHas('timetables', [
            'id' => $timetable1->id,
            'day' => 'Monday',
            'start_time' => '08:00',
        ]);
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable2->id,
            'day' => 'Tuesday',
            'start_time' => '10:10',
        ]);
    }

    public function test_timetable_swap_different_teachers(): void
    {
        $otherTeacherUser = User::factory()->create(['role' => 'teacher', 'name' => 'Jane Smith']);
        $otherTeacher = Teacher::create([
            'user_id' => $otherTeacherUser->id,
            'employee_id' => 'T-9999',
            'qualification' => 'B.Ed',
        ]);

        $otherClass = SchoolClass::create(['name' => 'JSS 3']);
        $otherSubject = Subject::create(['name' => 'Science']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $otherSubject->id,
        ]);

        $timetable1 = Timetable::create([
            'class_subject_id' => $this->classSubject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '08:40',
            'term_id' => $this->term->id,
        ]);

        $timetable2 = Timetable::create([
            'class_subject_id' => $otherClassSubject->id,
            'teacher_id' => $otherTeacher->id,
            'day' => 'Tuesday',
            'start_time' => '10:10',
            'end_time' => '10:50',
            'term_id' => $this->term->id,
        ]);

        $response = $this->post(route('admin.timetable.swap'), [
            'entry_a_id' => $timetable1->id,
            'entry_b_id' => $timetable2->id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('timetables', [
            'id' => $timetable1->id,
            'day' => 'Tuesday',
            'start_time' => '10:10',
        ]);
        $this->assertDatabaseHas('timetables', [
            'id' => $timetable2->id,
            'day' => 'Monday',
            'start_time' => '08:00',
        ]);
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
}
