<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Timetable;
use App\Models\User;
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
}
