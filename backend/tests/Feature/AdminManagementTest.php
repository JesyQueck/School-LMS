<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
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

        $class = SchoolClass::create([
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

    public function test_admin_can_create_a_student_with_parent(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $class = SchoolClass::create([
            'name' => 'JSS 1',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/students', [
            'name' => 'John Student',
            'email' => 'john.student@example.com',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'parent_email' => 'parent@example.com',
            'parent_name' => 'Mr. Student',
            'parent_phone' => '08012345678',
            'parent_occupation' => 'Engineer',
            'password' => 'Password123',
        ]);

        $response->assertRedirect('/admin/students');

        $this->assertDatabaseHas('users', [
            'name' => 'Mr. Student',
            'email' => 'parent@example.com',
            'role' => 'parent',
        ]);

        $parentUser = User::where('email', 'parent@example.com')->first();
        $this->assertNotNull($parentUser);

        $this->assertDatabaseHas('parents', [
            'user_id' => $parentUser->id,
            'phone' => '08012345678',
            'occupation' => 'Engineer',
        ]);
    }

    public function test_parent_name_validation_rejects_excessively_long_value(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $class = SchoolClass::create([
            'name' => 'JSS 1',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/students', [
            'name' => 'John Student',
            'email' => 'john.student@example.com',
            'class_id' => $class->id,
            'parent_email' => 'parent@example.com',
            'parent_name' => str_repeat('A', 300),
            'parent_phone' => '08012345678',
        ]);

        $response->assertSessionHasErrors(['parent_name']);
    }

    public function test_creating_student_does_not_create_default_class(): void
    {
        SchoolClass::create(['name' => 'JSS 1']);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $class = SchoolClass::first();

        $response = $this->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'test.student@example.com',
            'class_id' => $class->id,
        ]);

        $response->assertRedirect('/admin/students');

        $this->assertDatabaseMissing('classes', [
            'name' => 'Default Class',
        ]);
    }

    public function test_admin_can_create_student_with_first_and_last_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->post('/admin/students', [
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'class_id' => $class->id,
        ]);

        $response->assertRedirect('/admin/students');

        $student = Student::whereHas('user', fn ($q) => $q->where('email', 'john.doe@example.com'))->first();
        $this->assertNotNull($student);
        $this->assertSame('John', $student->first_name);
        $this->assertSame('Doe', $student->last_name);
        $this->assertSame('John Doe', $student->full_name);
    }
}
