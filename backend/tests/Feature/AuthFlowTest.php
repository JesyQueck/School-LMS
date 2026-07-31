<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_is_redirected_to_their_role_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'teacher',
        ]);

        $response = $this->post('/login', [
            'email' => 'teacher@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/teacher/dashboard');
        $this->assertAuthenticatedAs($user);
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
}
