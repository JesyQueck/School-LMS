<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    protected function createSessionWithTerm(string $name = '2026/2027'): Term
    {
        $session = AcademicSession::create([
            'name' => $name,
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
        ]);

        return Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-23',
            'is_current' => false,
        ]);
    }

    public function test_admin_can_update_term_dates(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $term = $this->createSessionWithTerm();

        $response = $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-09-15',
            'end_date' => '2026-12-30',
        ]);

        $response->assertRedirect(route('admin.academic'));
    }

    public function test_update_term_persists_new_dates(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $term = $this->createSessionWithTerm();

        $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-09-15',
            'end_date' => '2026-12-30',
        ]);

        $term->refresh();

        $this->assertEquals('2026-09-15', $term->start_date->toDateString());
        $this->assertEquals('2026-12-30', $term->end_date->toDateString());
    }

    public function test_update_term_redirects_to_academic_structure(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $term = $this->createSessionWithTerm();

        $response = $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-10-01',
            'end_date' => '2027-01-15',
        ]);

        $response->assertRedirect(route('admin.academic'));
        $response->assertSessionHas('status');
    }

    public function test_post_method_is_not_supported_for_term_update(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $term = $this->createSessionWithTerm();

        $response = $this->post(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-10-01',
            'end_date' => '2027-01-15',
        ]);

        $response->assertMethodNotAllowed();
    }

    public function test_put_method_is_supported_for_term_update(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $term = $this->createSessionWithTerm();

        $response = $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-10-01',
            'end_date' => '2027-01-15',
        ]);

        $response->assertRedirect(route('admin.academic'));
    }

    public function test_non_admin_user_cannot_update_term(): void
    {
        $term = $this->createSessionWithTerm();

        $user = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-10-01',
            'end_date' => '2027-01-15',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_update_term(): void
    {
        $term = $this->createSessionWithTerm();

        $response = $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-10-01',
            'end_date' => '2027-01-15',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_admin_can_create_term(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2027/2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-08-31',
            'is_current' => false,
        ]);

        $response = $this->post(route('admin.academic.terms.store'), [
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
            'start_date' => '2026-01-05',
            'end_date' => '2026-03-30',
        ]);

        $response->assertRedirect(route('admin.academic'));

        $this->assertDatabaseHas('terms', [
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
        ]);
    }

    public function test_update_term_validates_dates(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $term = $this->createSessionWithTerm();

        $response = $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2027-01-01',
            'end_date' => '2026-01-01',
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_update_term_with_partial_data(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $term = $this->createSessionWithTerm();

        $this->put(route('admin.academic.terms.update', $term), [
            'start_date' => '2026-09-15',
        ]);

        $term->refresh();

        $this->assertEquals('2026-09-15', $term->start_date->toDateString());
    }

    public function test_admin_can_update_session(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
        ]);

        $response = $this->put(route('admin.academic.sessions.update', $session), [
            'name' => '2026/2027 (Revised)',
            'start_date' => '2026-09-05',
            'end_date' => '2027-09-01',
        ]);

        $response->assertRedirect(route('admin.academic'));

        $session->refresh();
        $this->assertEquals('2026/2027 (Revised)', $session->name);
        $this->assertEquals('2026-09-05', $session->start_date->toDateString());
        $this->assertEquals('2027-09-01', $session->end_date->toDateString());
    }

    public function test_admin_can_correct_session_name_typo(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027 ',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
        ]);

        $response = $this->put(route('admin.academic.sessions.update', $session), [
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
        ]);

        $response->assertRedirect(route('admin.academic'));
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $session->id,
            'name' => '2026/2027',
        ]);
    }

    public function test_update_session_validates_name_is_required(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
        ]);

        $response = $this->put(route('admin.academic.sessions.update', $session), [
            'name' => '',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseHas('academic_sessions', [
            'name' => '2026/2027',
        ]);
    }

    public function test_update_session_validates_dates(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2017-08-31',
            'is_current' => true,
        ]);

        $response = $this->put(route('admin.academic.sessions.update', $session), [
            'name' => '2026/2027',
            'start_date' => '2027-01-01',
            'end_date' => '2026-01-01',
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_update_session_rejects_post_method(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2017-08-31',
            'is_current' => true,
        ]);

        $response = $this->post(route('admin.academic.sessions.update', $session), [
            'name' => '2027/2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-08-31',
        ]);

        $response->assertMethodNotAllowed();
    }

    public function test_session_update_form_is_present_on_academic_page(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2017-08-31',
            'is_current' => true,
        ]);

        $response = $this->get(route('admin.academic'));

        $response->assertOk();
        $response->assertSee('edit-session-'.$session->id);
        $response->assertSee('Save');
    }

    public function test_non_admin_cannot_update_session(): void
    {
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2017-08-31',
            'is_current' => true,
        ]);

        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $response = $this->put(route('admin.academic.sessions.update', $session), [
            'name' => '2027/2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-08-31',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_create_subject_with_multiple_classes(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $class1 = SchoolClass::create(['name' => 'JSS 1']);
        $class2 = SchoolClass::create(['name' => 'JSS 2']);

        $response = $this->post(route('admin.subjects.store'), [
            'name' => 'Mathematics',
            'class_ids' => [$class1->id, $class2->id],
        ]);

        $response->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseHas('subjects', ['name' => 'Mathematics']);
        $subject = Subject::where('name', 'Mathematics')->first();
        $this->assertDatabaseHas('class_subjects', ['subject_id' => $subject->id, 'class_id' => $class1->id]);
        $this->assertDatabaseHas('class_subjects', ['subject_id' => $subject->id, 'class_id' => $class2->id]);
    }

    public function test_admin_can_create_subject_without_classes(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->post(route('admin.subjects.store'), [
            'name' => 'Art',
        ]);

        $response->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseHas('subjects', ['name' => 'Art']);
    }

    public function test_duplicate_subject_name_is_rejected(): void
    {
        Subject::create(['name' => 'Mathematics']);

        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->post(route('admin.subjects.store'), [
            'name' => 'Mathematics',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('subjects', 1);
    }

    public function test_duplicate_subject_class_association_is_rejected(): void
    {
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $subject = Subject::create(['name' => 'Mathematics']);

        ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $admin = $this->adminUser();
        $this->actingAs($admin);

        $response = $this->post(route('admin.subjects.store'), [
            'name' => 'Mathematics',
            'class_ids' => [$class->id],
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertEquals(1, ClassSubject::where('subject_id', $subject->id)->where('class_id', $class->id)->count());
    }

    public function test_admin_can_edit_subject_name_and_classes(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $class1 = SchoolClass::create(['name' => 'JSS 1']);
        $class2 = SchoolClass::create(['name' => 'JSS 2']);
        $class3 = SchoolClass::create(['name' => 'SSS 1']);

        $subject = Subject::create(['name' => 'Mathematics']);
        ClassSubject::create(['class_id' => $class1->id, 'subject_id' => $subject->id]);

        $response = $this->put(route('admin.subjects.update', $subject), [
            'name' => 'Advanced Mathematics',
            'class_ids' => [$class2->id, $class3->id],
        ]);

        $response->assertRedirect(route('admin.subjects.index'));
        $subject->refresh();
        $this->assertEquals('Advanced Mathematics', $subject->name);
        $this->assertDatabaseMissing('class_subjects', ['subject_id' => $subject->id, 'class_id' => $class1->id]);
        $this->assertDatabaseHas('class_subjects', ['subject_id' => $subject->id, 'class_id' => $class2->id]);
        $this->assertDatabaseHas('class_subjects', ['subject_id' => $subject->id, 'class_id' => $class3->id]);
    }

    public function test_admin_can_delete_subject(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $subject = Subject::create(['name' => 'Art']);

        $response = $this->delete(route('admin.subjects.destroy', $subject));

        $response->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_non_admin_cannot_create_subject(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user);

        $response = $this->post(route('admin.subjects.store'), [
            'name' => 'Mathematics',
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_update_subject(): void
    {
        $subject = Subject::create(['name' => 'Mathematics']);

        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user);

        $response = $this->put(route('admin.subjects.update', $subject), [
            'name' => 'Updated',
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_subject(): void
    {
        $subject = Subject::create(['name' => 'Mathematics']);

        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user);

        $response = $this->delete(route('admin.subjects.destroy', $subject));

        $response->assertForbidden();
    }

    public function test_subjects_index_page_displays_subjects(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        Subject::create(['name' => 'Mathematics']);

        $response = $this->get(route('admin.subjects.index'));

        $response->assertOk();
        $response->assertSee('Subjects');
        $response->assertSee('Mathematics');
        $response->assertSee('Create Subject');
    }

    public function test_subjects_create_page_shows_form(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->get(route('admin.subjects.create'));

        $response->assertOk();
        $response->assertSee('Create Subject');
        $response->assertSee('Subject Name');
        $response->assertSee('JSS 1');
    }

    public function test_subjects_edit_page_shows_form(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $subject = Subject::create(['name' => 'Mathematics']);

        $response = $this->get(route('admin.subjects.edit', $subject));

        $response->assertOk();
        $response->assertSee('Edit Subject');
        $response->assertSee('Mathematics');
    }

    public function test_non_admin_cannot_access_subjects_create_page(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user);

        $response = $this->get(route('admin.subjects.create'));

        $response->assertForbidden();
    }
}
