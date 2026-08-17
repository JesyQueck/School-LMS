<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
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
}
