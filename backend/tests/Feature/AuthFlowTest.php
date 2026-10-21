<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ImportCredential;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
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

    public function test_admin_can_login_with_email(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.login@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'identifier' => '  ADMIN.LOGIN@example.com ',
            'password' => 'secret123',
        ])->assertRedirect('/admin/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_teacher_can_login_with_staff_id_or_email(): void
    {
        $teacherUser = User::factory()->create([
            'email' => 'teacher.id@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
        ]);
        Teacher::create(['user_id' => $teacherUser->id, 'employee_id' => 'STAFF-009']);

        $this->post('/login', [
            'identifier' => ' staff-009 ',
            'password' => 'secret123',
        ])->assertRedirect('/teacher/dashboard');
        $this->assertAuthenticatedAs($teacherUser);

        auth()->logout();
        $this->post('/login', [
            'identifier' => 'TEACHER.ID@example.com',
            'password' => 'secret123',
        ])->assertRedirect('/teacher/dashboard');
        $this->assertAuthenticatedAs($teacherUser);
    }

    public function test_student_can_login_with_admission_number_without_email(): void
    {
        $studentUser = User::factory()->create([
            'email' => null,
            'password' => Hash::make('secret123'),
            'role' => 'student',
        ]);
        $class = SchoolClass::create(['name' => 'JSS Login']);
        Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-LOGIN-009',
            'class_id' => $class->id,
        ]);

        $this->post('/login', [
            'identifier' => 'adm-login-009',
            'password' => 'secret123',
        ])->assertRedirect('/student/dashboard');

        $this->assertAuthenticatedAs($studentUser);
    }

    public function test_parent_can_login_with_profile_id_or_email_and_retains_all_children(): void
    {
        $parentUser = User::factory()->create([
            'email' => 'guardian@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'parent',
        ]);
        $parent = ParentProfile::create(['user_id' => $parentUser->id]);
        $class = SchoolClass::create(['name' => 'JSS Parent']);
        foreach (['ADM-CHILD-1', 'ADM-CHILD-2'] as $admissionNo) {
            $studentUser = User::factory()->create(['role' => 'student']);
            $student = Student::create([
                'user_id' => $studentUser->id,
                'admission_no' => $admissionNo,
                'class_id' => $class->id,
            ]);
            $parent->students()->attach($student->id);
        }

        $this->post('/login', [
            'identifier' => 'PARENT-'.$parent->id,
            'password' => 'secret123',
        ])->assertRedirect('/parent/dashboard');
        $this->assertAuthenticatedAs($parentUser);
        $this->assertCount(2, $parent->students);

        auth()->logout();
        $this->post('/login', [
            'identifier' => 'guardian@example.com',
            'password' => 'secret123',
        ])->assertRedirect('/parent/dashboard');
        $this->assertAuthenticatedAs($parentUser);
    }

    public function test_incorrect_password_and_unknown_identifier_have_generic_failures(): void
    {
        $user = User::factory()->create([
            'email' => 'generic-failure@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        foreach ([
            ['identifier' => $user->email, 'password' => 'wrong-password'],
            ['identifier' => 'unknown-login-id', 'password' => 'wrong-password'],
        ] as $credentials) {
            $response = $this->from('/login')->post('/login', $credentials);
            $response->assertSessionHasErrors(['identifier' => __('auth.failed')]);
            $this->assertGuest();
        }
    }

    public function test_student_email_is_not_a_login_identifier(): void
    {
        $user = User::factory()->create([
            'email' => 'student.email@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'student',
        ]);

        $this->from('/login')->post('/login', [
            'identifier' => $user->email,
            'password' => 'secret123',
        ])->assertSessionHasErrors(['identifier' => __('auth.failed')]);
        $this->assertGuest();
    }

    public function test_ambiguous_cross_role_identifier_is_rejected(): void
    {
        $teacherUser = User::factory()->create([
            'email' => 'ambiguous@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
        ]);
        Teacher::create(['user_id' => $teacherUser->id, 'employee_id' => 'SHARED-LOGIN']);

        $studentUser = User::factory()->create([
            'password' => Hash::make('secret123'),
            'role' => 'student',
        ]);
        $class = SchoolClass::create(['name' => 'JSS Shared']);
        Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'shared-login',
            'class_id' => $class->id,
        ]);

        $this->from('/login')->post('/login', [
            'identifier' => 'SHARED-LOGIN',
            'password' => 'secret123',
        ])->assertSessionHasErrors(['identifier' => __('auth.failed')]);
        $this->assertGuest();
    }

    public function test_remember_me_is_preserved_and_password_change_is_required(): void
    {
        $teacherUser = User::factory()->create([
            'email' => 'remember.teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'needs_password_change' => true,
        ]);

        $this->post('/login', [
            'identifier' => $teacherUser->email,
            'password' => 'secret123',
            'remember' => '1',
        ])->assertRedirect('/change-password');

        $this->assertAuthenticatedAs($teacherUser);
        $this->assertNotNull($teacherUser->fresh()->remember_token);
    }

    public function test_session_token_is_regenerated_after_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'session.teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
        ]);

        $this->withSession(['_token' => 'before-login-token'])
            ->post('/login', [
                'identifier' => $user->email,
                'password' => 'secret123',
            ])
            ->assertSessionHas('_token', fn ($token) => $token !== 'before-login-token');
    }

    public function test_intended_redirect_is_preserved_after_identifier_login(): void
    {
        $teacherUser = User::factory()->create([
            'email' => 'intended.teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
            'needs_password_change' => false,
        ]);

        $this->get('/teacher/dashboard')->assertRedirect('/login');
        $this->post('/login', [
            'identifier' => $teacherUser->email,
            'password' => 'secret123',
        ])->assertRedirect('/teacher/dashboard');
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

    public function test_parent_can_request_a_reset_link_but_other_roles_cannot(): void
    {
        Notification::fake();
        $parent = User::factory()->create([
            'email' => 'parent.reset@example.com',
            'role' => 'parent',
        ]);
        User::factory()->create([
            'email' => 'teacher.reset@example.com',
            'role' => 'teacher',
        ]);

        $parentResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => ' PARENT.RESET@example.com ',
        ]);
        $parentResponse->assertSessionHas('status', 'If an eligible parent account exists for that email address, a password reset link will be sent.');
        Notification::assertSentTo($parent, ResetPassword::class);

        $teacherResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'teacher.reset@example.com',
        ]);
        $teacherResponse->assertSessionHas('status', $parentResponse->getSession()->get('status'));
        Notification::assertNotSentTo(User::where('email', 'teacher.reset@example.com')->firstOrFail(), ResetPassword::class);
    }

    public function test_unknown_and_non_parent_emails_receive_the_same_forgot_password_response(): void
    {
        User::factory()->create([
            'email' => 'staff.no.reset@example.com',
            'role' => 'admin',
        ]);

        $staff = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'staff.no.reset@example.com',
        ]);
        $unknown = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'unknown.reset@example.com',
        ]);

        $this->assertSame($staff->getSession()->get('status'), $unknown->getSession()->get('status'));
    }

    public function test_parent_can_reset_password_once_and_the_reset_is_audited(): void
    {
        $parent = User::factory()->create([
            'email' => 'parent.reset.complete@example.com',
            'password' => Hash::make('Temporary123!'),
            'role' => 'parent',
            'needs_password_change' => true,
        ]);
        $token = Password::createToken($parent);

        $response = $this->post('/reset-password', [
            'email' => $parent->email,
            'token' => $token,
            'password' => 'ParentNewPassword123!',
            'password_confirmation' => 'ParentNewPassword123!',
        ]);

        $response->assertRedirect('/login');
        $parent->refresh();
        $this->assertTrue(Hash::check('ParentNewPassword123!', $parent->password));
        $this->assertFalse($parent->needs_password_change);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $parent->email]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'parent.password_reset',
            'target_model' => User::class,
            'target_id' => $parent->id,
        ]);
        $audit = AuditLog::where('action', 'parent.password_reset')->firstOrFail();
        $this->assertSame(['role' => 'parent'], $audit->new_value);

        $this->post('/login', [
            'identifier' => $parent->email,
            'password' => 'ParentNewPassword123!',
        ])->assertRedirect('/parent/dashboard');
        $this->assertAuthenticatedAs($parent);

        $this->post('/logout')->assertRedirect('/login');
        $this->post('/reset-password', [
            'email' => $parent->email,
            'token' => $token,
            'password' => 'AnotherNewPassword123!',
            'password_confirmation' => 'AnotherNewPassword123!',
        ])->assertSessionHasErrors(['email' => 'The password reset link is invalid or has expired.']);
        $this->assertTrue(Hash::check('ParentNewPassword123!', $parent->fresh()->password));
    }

    public function test_expired_parent_reset_token_is_rejected(): void
    {
        $parent = User::factory()->create([
            'email' => 'parent.reset.expired@example.com',
            'password' => Hash::make('Temporary123!'),
            'role' => 'parent',
        ]);
        $token = Password::createToken($parent);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $parent->email)
            ->update(['created_at' => now()->subMinutes(config('auth.passwords.users.expire') + 1)]);

        $this->post('/reset-password', [
            'email' => $parent->email,
            'token' => $token,
            'password' => 'ParentNewPassword123!',
            'password_confirmation' => 'ParentNewPassword123!',
        ])->assertSessionHasErrors(['email' => 'The password reset link is invalid or has expired.']);

        $this->assertTrue(Hash::check('Temporary123!', $parent->fresh()->password));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'parent.password_reset']);
    }

    public function test_non_parent_cannot_complete_password_reset_even_with_a_valid_broker_token(): void
    {
        $student = User::factory()->create([
            'email' => 'student.reset.denied@example.com',
            'password' => Hash::make('Temporary123!'),
            'role' => 'student',
        ]);
        $token = Password::createToken($student);

        $this->post('/reset-password', [
            'email' => $student->email,
            'token' => $token,
            'password' => 'StudentNewPassword123!',
            'password_confirmation' => 'StudentNewPassword123!',
        ])->assertSessionHasErrors(['email' => 'The password reset link is invalid or has expired.']);

        $this->assertTrue(Hash::check('Temporary123!', $student->fresh()->password));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'parent.password_reset']);
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

    private function createUserForPasswordChange(string $role): User
    {
        return User::factory()->create([
            'name' => ucfirst($role).' User',
            'email' => $role.'@example.com',
            'password' => Hash::make('OldPass123!'),
            'role' => $role,
            'is_active' => true,
            'needs_password_change' => true,
        ]);
    }

    public function test_admin_can_successfully_change_password(): void
    {
        $user = $this->createUserForPasswordChange('admin');

        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('status', 'Password changed successfully.');
        $this->assertFalse($user->fresh()->needs_password_change);
        $this->assertTrue(Hash::check('NewPass123!', $user->fresh()->password));
    }

    public function test_teacher_can_successfully_change_password(): void
    {
        $user = $this->createUserForPasswordChange('teacher');
        $credential = ImportCredential::create([
            'role' => 'teacher',
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'legacy-password',
            'temporary_password' => 'TemporaryTeacher123!',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertRedirect('/teacher/dashboard');
        $response->assertSessionHas('status', 'Password changed successfully.');
        $this->assertNull($credential->fresh()->temporary_password);
        $this->assertNull($credential->fresh()->password);
        $this->assertNull(\Illuminate\Support\Facades\DB::table('import_credentials')->where('id', $credential->id)->value('temporary_password'));
        $this->assertFalse($user->fresh()->needs_password_change);
    }

    public function test_student_can_successfully_change_password(): void
    {
        $user = $this->createUserForPasswordChange('student');
        $credential = ImportCredential::create([
            'role' => 'student',
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'legacy-password',
            'temporary_password' => 'TemporaryStudent123!',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertRedirect('/student/dashboard');
        $response->assertSessionHas('status', 'Password changed successfully.');
        $this->assertNull($credential->fresh()->temporary_password);
        $this->assertNull($credential->fresh()->password);
        $this->assertFalse($user->fresh()->needs_password_change);
    }

    public function test_parent_can_successfully_change_password(): void
    {
        $user = $this->createUserForPasswordChange('parent');

        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertRedirect('/parent/dashboard');
        $response->assertSessionHas('status', 'Password changed successfully.');
    }

    public function test_invalid_current_password_is_rejected(): void
    {
        $user = $this->createUserForPasswordChange('admin');

        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'WrongPassword',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OldPass123!', $user->fresh()->password));
        $this->assertTrue($user->fresh()->needs_password_change);
    }

    public function test_failed_password_change_preserves_encrypted_temporary_credential(): void
    {
        $user = $this->createUserForPasswordChange('teacher');
        $credential = ImportCredential::create([
            'role' => 'teacher',
            'name' => $user->name,
            'email' => $user->email,
            'password' => null,
            'temporary_password' => 'TemporaryTeacher123!',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/change-password', [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewTeacherPassword123!',
            'password_confirmation' => 'NewTeacherPassword123!',
        ])->assertSessionHasErrors('current_password');

        $this->assertSame('TemporaryTeacher123!', $credential->fresh()->temporary_password);
        $this->assertTrue($user->fresh()->needs_password_change);
    }

    public function test_mismatched_password_confirmation_is_rejected(): void
    {
        $user = $this->createUserForPasswordChange('admin');

        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'DifferentPass123!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('OldPass123!', $user->fresh()->password));
        $this->assertTrue($user->fresh()->needs_password_change);
    }

    public function test_password_change_page_is_accessible_for_user_requiring_change(): void
    {
        $user = $this->createUserForPasswordChange('admin');

        $this->actingAs($user);

        $response = $this->get('/change-password');

        $response->assertStatus(200);
        $response->assertViewIs('auth.password-change');
    }

    public function test_password_change_page_is_accessible_to_users_not_requiring_change(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'needs_password_change' => false,
            'password' => Hash::make('OldPass123!'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/change-password');

        $response->assertStatus(200);
        $response->assertViewIs('auth.password-change');
    }

    public function test_password_change_redirect_does_not_reference_nonexistent_route(): void
    {
        $user = $this->createUserForPasswordChange('admin');

        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/dashboard');
    }
}
