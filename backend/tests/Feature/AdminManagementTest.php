<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/classes', [
            'name' => 'JSS 1',
            'level' => 'JSS 1',
        ]);

        $response->assertRedirect('/admin/classes');
        $this->assertDatabaseHas('classes', ['name' => 'JSS 1']);
    }

    public function test_admin_can_create_a_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->post('/admin/teachers', [
            'user_id' => $user->id,
            'specialization' => 'Mathematics',
        ]);

        $response->assertRedirect('/admin/teachers');
        $this->assertDatabaseHas('teachers', ['user_id' => $user->id, 'qualification' => 'Mathematics']);
    }

    public function test_admin_can_create_a_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->post('/admin/students', [
            'user_id' => $user->id,
            'admission_number' => 'ADM001',
        ]);

        $response->assertRedirect('/admin/students');
        $this->assertDatabaseHas('students', ['user_id' => $user->id, 'admission_no' => 'ADM001']);
    }
}
