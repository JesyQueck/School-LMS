<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_student_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'teacher', 'name' => 'Mr. Smith']);
        $this->actingAs($user);

        $response = $this->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->create('photo.png', 150, 'image/png'),
        ]);

        $response->assertRedirect(route('settings.profile'));
        $this->assertNotNull($user->fresh()->profile_photo);
        $this->assertStringContainsString('profile-photos', $user->fresh()->profile_photo);
        Storage::disk('public')->assertExists($user->fresh()->profile_photo);
    }

    public function test_student_cannot_upload_their_own_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'student', 'name' => 'Amina Bello']);
        $this->actingAs($user);

        $this->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->create('avatar.jpg', 200, 'image/jpeg'),
        ])->assertStatus(403);

        $this->assertNull($user->fresh()->profile_photo);
    }

    public function test_admin_can_upload_a_student_profile_photo(): void
    {
        Storage::fake('public');

        $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Amina Bello']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-001',
            'first_name' => 'Amina',
            'last_name' => 'Bello',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('admin.students.photo.update', $student), [
            'photo' => UploadedFile::fake()->create('avatar.jpg', 200, 'image/jpeg'),
        ]);

        $response->assertRedirect(route('admin.students.edit', $student));
        $this->assertNotNull($studentUser->fresh()->profile_photo);
        $this->assertStringContainsString('profile-photos', $studentUser->fresh()->profile_photo);
        Storage::disk('public')->assertExists($studentUser->fresh()->profile_photo);
    }

    public function test_admin_can_remove_a_student_profile_photo(): void
    {
        Storage::fake('public');

        $studentUser = User::factory()->create([
            'role' => 'student',
            'name' => 'Amina Bello',
            'profile_photo' => 'profile-photos/old.jpg',
        ]);
        Storage::disk('public')->put('profile-photos/old.jpg', 'fake-contents');

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-001',
            'first_name' => 'Amina',
            'last_name' => 'Bello',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->delete(route('admin.students.photo.destroy', $student));

        $response->assertRedirect(route('admin.students.edit', $student));
        $this->assertNull($studentUser->fresh()->profile_photo);
        Storage::disk('public')->assertMissing('profile-photos/old.jpg');
    }

    public function test_profile_photo_upload_rejects_non_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user);

        $this->post(route('profile.photo.update'), ['photo' => 'not-an-image'])
            ->assertSessionHasErrors('photo');
    }

    public function test_profile_photo_upload_requires_authentication(): void
    {
        $this->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg'),
        ])->assertRedirect('/login');
    }

    public function test_teacher_can_remove_their_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'teacher',
            'profile_photo' => 'profile-photos/old.jpg',
        ]);
        Storage::disk('public')->put('profile-photos/old.jpg', 'fake-contents');
        $this->actingAs($user);

        $response = $this->delete(route('profile.photo.destroy'));

        $response->assertRedirect(route('settings.profile'));
        $this->assertNull($user->fresh()->profile_photo);
        Storage::disk('public')->assertMissing('profile-photos/old.jpg');
    }
}
