<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ImportCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BursarRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_bursar_can_login_with_email(): void
    {
        $bursar = User::factory()->create([
            'email' => 'bursar@example.com',
            'staff_id' => 'B-LOGIN-001',
            'password' => Hash::make('secret123'),
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'identifier' => 'b-login-001',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/bursar/dashboard');
        $this->assertAuthenticatedAs($bursar);
    }

    public function test_bursar_can_login_with_whitespace_email(): void
    {
        $bursar = User::factory()->create([
            'email' => 'bursar.test@example.com',
            'staff_id' => 'B-LOGIN-002',
            'password' => Hash::make('secret123'),
            'role' => 'bursar',
        ]);

        $this->post('/login', [
            'identifier' => ' b-login-002 ',
            'password' => 'secret123',
        ])->assertRedirect('/bursar/dashboard');

        $this->assertAuthenticatedAs($bursar);
    }

    public function test_bursar_dashboard_is_accessible(): void
    {
        $bursar = User::factory()->create([
            'email' => 'bursar.dash@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'bursar',
            'needs_password_change' => false,
        ]);

        $this->actingAs($bursar);

        $response = $this->get('/bursar/dashboard');

        $response->assertOk();
        $response->assertViewIs('bursar.dashboard');
    }

    public function test_bursar_navigation_pages_are_accessible(): void
    {
        $bursar = User::factory()->create([
            'email' => 'bursar.nav@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'bursar',
            'needs_password_change' => false,
        ]);

        $this->actingAs($bursar);

        $pages = [
            '/bursar/student-fees' => 'bursar.student-fees',
            '/bursar/fee-collections' => 'bursar.fee-collections',
            '/bursar/staff-salaries' => 'bursar.staff-salaries',
            '/bursar/expenses' => 'bursar.expenses',
        ];

        foreach ($pages as $url => $viewName) {
            $response = $this->get($url);
            $response->assertOk();
            $response->assertViewIs($viewName);
        }
    }

    public function test_admin_cannot_access_bursar_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/bursar/dashboard');
        $response->assertForbidden();
    }

    public function test_teacher_cannot_access_bursar_routes(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
        ]);

        $this->actingAs($teacher);

        $response = $this->get('/bursar/dashboard');
        $response->assertForbidden();
    }

    public function test_student_cannot_access_bursar_routes(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($student);

        $response = $this->get('/bursar/dashboard');
        $response->assertForbidden();
    }

    public function test_parent_cannot_access_bursar_routes(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $this->actingAs($parent);

        $response = $this->get('/bursar/dashboard');
        $response->assertForbidden();
    }

    public function test_guest_cannot_access_bursar_routes(): void
    {
        $response = $this->get('/bursar/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_create_bursar_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/accounts', [
            'type' => 'bursar',
            'name' => 'John Bursar',
            'email' => 'john.bursar@example.com',
            'staff_id' => 'B-009',
            'phone' => '08012345678',
        ]);

        $response->assertRedirect('/admin/accounts/create');
        $response->assertSessionHas('status', 'Account created successfully.');

        $this->assertDatabaseHas('users', [
            'email' => 'john.bursar@example.com',
            'staff_id' => 'B-009',
            'role' => 'bursar',
        ]);

        $bursar = User::where('email', 'john.bursar@example.com')->first();
        $this->assertNotNull($bursar);
        $this->assertTrue($bursar->needs_password_change);

        $this->assertDatabaseHas('import_credentials', [
            'user_id' => $bursar->id,
            'role' => 'bursar',
        ]);
    }

    public function test_bursar_credentials_show_in_credentials_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create bursar account
        $this->post('/admin/accounts', [
            'type' => 'bursar',
            'name' => 'Jane Bursar',
            'email' => 'jane.bursar@example.com',
            'staff_id' => 'B-004',
        ]);

        $response = $this->get('/admin/accounts/credentials');
        $response->assertOk();
        $response->assertSee('Bursar / Financial Officer');
        $response->assertSee('jane.bursar@example.com');
    }

    public function test_bursar_login_creates_audit_entry(): void
    {
        $bursar = User::factory()->create([
            'email' => 'audit.bursar@example.com',
            'staff_id' => 'B-005',
            'password' => Hash::make('secret123'),
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'identifier' => 'B-005',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $bursar->id,
            'action' => 'user.login',
        ]);
    }

    public function test_bursar_logout_creates_audit_entry(): void
    {
        $bursar = User::factory()->create([
            'email' => 'audit.bursar.logout@example.com',
            'staff_id' => 'B-006',
            'password' => Hash::make('secret123'),
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'identifier' => 'B-006',
            'password' => 'secret123',
        ]);

        $this->actingAs($bursar);
        $this->post('/logout');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $bursar->id,
            'action' => 'user.logout',
        ]);
    }

    public function test_bursar_can_change_password(): void
    {
        $bursar = User::factory()->create([
            'email' => 'bursar.pwd@example.com',
            'password' => Hash::make('OldPass123!'),
            'role' => 'bursar',
            'needs_password_change' => true,
        ]);

        $this->actingAs($bursar);

        $response = $this->post('/change-password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertRedirect('/bursar/dashboard');
        $response->assertSessionHas('status', 'Password changed successfully.');
        $this->assertFalse($bursar->fresh()->needs_password_change);
        $this->assertTrue(Hash::check('NewPass123!', $bursar->fresh()->password));
    }

    public function test_bursar_sidebar_shows_correct_navigation(): void
    {
        $bursar = User::factory()->create([
            'role' => 'bursar',
        ]);

        $this->actingAs($bursar);

        $response = $this->get('/bursar/dashboard');
        $response->assertOk();

        // Check for bursar-specific sidebar items
        $response->assertSee('Student Fee Payments');
        $response->assertSee('Fee Collections & Reports');
        $response->assertSee('Staff Salary Payments');
        $response->assertSee('School Expenses');
    }

    public function test_bursar_login_identifier_uses_staff_id(): void
    {
        $bursar = User::factory()->create([
            'email' => 'identifier.bursar@example.com',
            'staff_id' => 'B-007',
            'role' => 'bursar',
        ]);

        $this->assertEquals('B-007', $bursar->loginIdentifier());
    }

    public function test_inactive_bursar_cannot_login(): void
    {
        $bursar = User::factory()->create([
            'email' => 'inactive.bursar@example.com',
            'staff_id' => 'B-008',
            'password' => Hash::make('secret123'),
            'role' => 'bursar',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'identifier' => 'B-008',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['identifier' => __('auth.failed')]);
        $this->assertGuest();
    }
}