<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'teacher@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/teacher/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_surfaces_an_error_message_on_the_login_page(): void
    {
        $response = $this
            ->from('/login')
            ->followingRedirects()
            ->post('/login', [
                'email' => 'missing@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee(__('auth.failed'));
        $this->assertGuest();
    }

    public function test_password_reset_status_is_shown_on_the_login_page(): void
    {
        $response = $this->withSession(['status' => 'password-reset-notification'])
            ->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('password-reset-notification');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'teacher@example.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['email' => __('auth.failed')]);
    }

    public function test_inactive_user_is_not_left_authenticated_after_failed_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'teacher@example.com',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_user_receives_redirect_response_after_failed_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'teacher@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_forgot_password_page_is_available(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee('Forgot Password');
    }

    public function test_guests_are_redirected_to_login_for_protected_dashboards(): void
    {
        $response = $this->get('/teacher/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_users_without_the_required_role_receive_forbidden(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($user);

        $response = $this->get('/teacher/dashboard');

        $response->assertForbidden();
    }

    public function test_login_succeeds_when_under_rate_limit(): void
    {
        $user = User::factory()->create([
            'email' => 'under-limit@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'under-limit@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/teacher/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_attempts_are_counted(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'counted-'.uniqid().'@example.com',
                'password' => 'wrong-password',
            ]);

            $response->assertSessionHasErrors(['email' => __('auth.failed')]);
        }

        $this->assertGuest();
    }

    public function test_throttle_after_five_failed_attempts_per_minute(): void
    {
        User::factory()->create([
            'email' => 'throttled@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'throttled@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'throttled@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
        $this->assertGuest();
    }

    public function test_throttle_response_status_code_is_429(): void
    {
        User::factory()->create([
            'email' => 'throttle-status@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'throttle-status@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'throttle-status@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_successful_login_possible_when_under_limit(): void
    {
        $user = User::factory()->create([
            'email' => 'success-under-limit@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'success-under-limit@example.com',
            'password' => 'wrong-password',
        ]);

        $response = $this->post('/login', [
            'email' => 'success-under-limit@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/teacher/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_attempts_are_counted_by_rate_limiter(): void
    {
        User::factory()->create([
            'email' => 'inactive-rate@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => false,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'inactive-rate@example.com',
                'password' => 'secret123',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'inactive-rate@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(429);
        $this->assertGuest();
    }

    public function test_successful_login_creates_login_audit_entry(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-login@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'audit-login@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/teacher/dashboard');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user.login',
        ]);
    }

    public function test_login_audit_entry_belongs_to_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-owner@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'audit-owner@example.com',
            'password' => 'secret123',
        ]);

        $auditLog = AuditLog::where('action', 'user.login')->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($user->id, $auditLog->user_id);
    }

    public function test_login_audit_records_ip_address(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-ip@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->withHeaders(['REMOTE_ADDR' => '10.0.0.50'])->post('/login', [
            'email' => 'audit-ip@example.com',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.login',
            'ip_address' => '10.0.0.50',
        ]);
    }

    public function test_login_audit_records_user_agent(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-ua@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)';

        $this->withHeaders(['User-Agent' => $userAgent])->post('/login', [
            'email' => 'audit-ua@example.com',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.login',
            'user_agent' => $userAgent,
        ]);
    }

    public function test_failed_login_does_not_create_login_audit_entry(): void
    {
        User::factory()->create([
            'email' => 'audit-fail@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'audit-fail@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'user.login',
        ]);
    }

    public function test_inactive_user_login_does_not_create_login_audit_entry(): void
    {
        User::factory()->create([
            'email' => 'audit-inactive@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'audit-inactive@example.com',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'user.login',
        ]);
    }

    public function test_successful_logout_creates_logout_audit_entry(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-logout@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'audit-logout@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user.logout',
        ]);
    }

    public function test_logout_audit_entry_belongs_to_user_who_logged_out(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-logout-owner@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'audit-logout-owner@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($user);

        $this->post('/logout');

        $auditLog = AuditLog::where('action', 'user.logout')->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($user->id, $auditLog->user_id);
    }

    public function test_logout_audit_records_ip_address_and_user_agent(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-logout-ua@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)';

        $this->withHeaders(['User-Agent' => $userAgent])->post('/login', [
            'email' => 'audit-logout-ua@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($user);

        $this->withHeaders(['User-Agent' => $userAgent])->post('/logout');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user.logout',
            'user_agent' => $userAgent,
        ]);
    }
}
