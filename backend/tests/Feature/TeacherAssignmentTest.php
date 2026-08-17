<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function createSetup(): array
    {
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

        $class1 = SchoolClass::create(['name' => 'JSS 1']);
        $class2 = SchoolClass::create(['name' => 'JSS 2']);

        $math = Subject::create(['name' => 'Mathematics']);
        $science = Subject::create(['name' => 'Basic Science']);

        $cs1 = ClassSubject::create([
            'class_id' => $class1->id,
            'subject_id' => $math->id,
            'is_compulsory' => true,
        ]);

        $cs2 = ClassSubject::create([
            'class_id' => $class2->id,
            'subject_id' => $math->id,
            'is_compulsory' => true,
        ]);

        $cs3 = ClassSubject::create([
            'class_id' => $class1->id,
            'subject_id' => $science->id,
            'is_compulsory' => true,
        ]);

        $teacher1User = User::factory()->create(['role' => 'teacher', 'name' => 'Sarah Adeyemi']);
        $teacher1 = Teacher::create([
            'user_id' => $teacher1User->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $teacher2User = User::factory()->create(['role' => 'teacher', 'name' => 'Daniel Okafor']);
        $teacher2 = Teacher::create([
            'user_id' => $teacher2User->id,
            'employee_id' => 'T-1002',
            'qualification' => 'B.Ed',
        ]);

        return [
            'session' => $session,
            'term' => $term,
            'class1' => $class1,
            'class2' => $class2,
            'math' => $math,
            'science' => $science,
            'cs_math_jss1' => $cs1,
            'cs_math_jss2' => $cs2,
            'cs_science_jss1' => $cs3,
            'teacher1' => $teacher1,
            'teacher1User' => $teacher1User,
            'teacher2' => $teacher2,
            'teacher2User' => $teacher2User,
        ];
    }

    public function test_assignment_index_page_shows_class_and_subject_dropdowns(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->get(route('admin.assignments'));

        $response->assertOk();
        $response->assertSee('Assign Subject');
        $response->assertSee('class_id_sa');
        $response->assertSee('class_subject_id_sa');
    }

    public function test_teacher_a_can_be_assigned_mathematics_to_jss1(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response->assertRedirect(route('admin.assignments'));
        $this->assertDatabaseHas('teacher_class_subjects', [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);
    }

    public function test_teacher_b_can_be_assigned_mathematics_to_jss2(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher2']->id,
            'class_subject_id' => $data['cs_math_jss2']->id,
        ]);

        $response->assertRedirect(route('admin.assignments'));
        $this->assertDatabaseHas('teacher_class_subjects', [
            'teacher_id' => $data['teacher2']->id,
            'class_subject_id' => $data['cs_math_jss2']->id,
        ]);
    }

    public function test_same_teacher_can_teach_another_subject_in_same_class(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_science_jss1']->id,
        ]);

        $response->assertRedirect(route('admin.assignments'));
        $this->assertDatabaseHas('teacher_class_subjects', [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_science_jss1']->id,
        ]);
    }

    public function test_same_teacher_can_teach_same_subject_in_multiple_classes(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss2']->id,
        ]);

        $response->assertRedirect(route('admin.assignments'));
        $this->assertDatabaseHas('teacher_class_subjects', [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss2']->id,
        ]);
    }

    public function test_exact_duplicate_teacher_class_subject_is_rejected(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response->assertSessionHasErrors(['class_subject_id']);
        $this->assertEquals(1, TeacherClassSubject::where('teacher_id', $data['teacher1']->id)
            ->where('class_subject_id', $data['cs_math_jss1']->id)
            ->count());
    }

    public function test_non_admin_cannot_access_assignment_index(): void
    {
        $data = $this->createSetup();

        $this->actingAs($data['teacher1User']);

        $response = $this->get(route('admin.assignments'));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_store_assignment(): void
    {
        $data = $this->createSetup();

        $this->actingAs($data['teacher1User']);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('teacher_class_subjects', [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);
    }

    public function test_non_admin_cannot_destroy_assignment(): void
    {
        $data = $this->createSetup();

        $assignment = TeacherClassSubject::create([
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => $data['cs_math_jss1']->id,
            'is_active' => true,
        ]);

        $this->actingAs($data['teacher2User']);

        $response = $this->delete(route('admin.assignments.destroy', $assignment->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('teacher_class_subjects', ['id' => $assignment->id]);
    }

    public function test_subject_dropdown_only_shows_subjects_for_selected_class(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->get(route('admin.assignments'));

        $response->assertOk();
        $response->assertSee('data-class-id', false);
        $response->assertSee('Mathematics');
        $response->assertSee('Basic Science');
    }

    public function test_assignment_store_validates_teacher_exists(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => 99999,
            'class_subject_id' => $data['cs_math_jss1']->id,
        ]);

        $response->assertSessionHasErrors(['teacher_id']);
    }

    public function test_assignment_store_validates_class_subject_exists(): void
    {
        $data = $this->createSetup();
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->post(route('admin.assignments.store'), [
            'teacher_id' => $data['teacher1']->id,
            'class_subject_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['class_subject_id']);
    }
}
