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
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/classes', [
            'name' => 'JSS 1',
        ]);

        $response->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('classes', [
            'name' => 'JSS 1',
        ]);
    }

    public function test_admin_can_create_a_teacher(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/teachers', [
            'name' => 'John Teacher',
            'email' => 'john.teacher@example.com',
            'phone' => '08012345678',
            'qualification' => 'B.Sc. Mathematics',
            'password' => 'Password123',
        ]);

        $response->assertRedirect('/admin/teachers');

        $this->assertDatabaseHas('users', [
            'name' => 'John Teacher',
            'email' => 'john.teacher@example.com',
            'role' => 'teacher',
        ]);

        $teacherUser = User::where(
            'email',
            'john.teacher@example.com'
        )->first();

        $this->assertNotNull($teacherUser);

        $this->assertDatabaseHas('teachers', [
            'user_id' => $teacherUser->id,
            'qualification' => 'B.Sc. Mathematics',
        ]);
    }

    public function test_admin_can_create_a_student(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $class = \App\Models\SchoolClass::create([
            'name' => 'JSS 1',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/students', [
            'name' => 'Jane Student',
            'email' => 'jane.student@example.com',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'female',
            'password' => 'Password123',
        ]);

        $response->assertRedirect('/admin/students');

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Student',
            'email' => 'jane.student@example.com',
            'role' => 'student',
        ]);

        $studentUser = User::where(
            'email',
            'jane.student@example.com'
        )->first();

        $this->assertNotNull($studentUser);

        $this->assertDatabaseHas('students', [
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
        ]);
    }
}
