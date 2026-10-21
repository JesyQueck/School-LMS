<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
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

    public function test_profile_photo_controls_render_below_the_avatar_not_over_it(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'profile_photo' => 'profile-photos/current.jpg',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('settings.profile'));

        $response->assertOk();
        $response->assertSee('Choose photo');
        $response->assertSee('Remove photo');
        $response->assertSee('flex flex-wrap items-center justify-center gap-2', false);
        $response->assertDontSee('absolute bottom-0 right-0', false);
    }

    public function test_failed_profile_photo_storage_preserves_existing_photo_and_returns_an_error(): void
    {
        Storage::fake('public');
        $publicDisk = Storage::disk('public');
        $publicDisk->put('profile-photos/current.jpg', 'existing-photo');

        $user = User::factory()->create([
            'role' => 'teacher',
            'profile_photo' => 'profile-photos/current.jpg',
        ]);
        $this->actingAs($user);

        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('putFile')
            ->once()
            ->with('profile-photos', Mockery::type(UploadedFile::class))
            ->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $response = $this->from(route('settings.profile'))->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('replacement.jpg'),
        ]);

        $response->assertRedirect(route('settings.profile'));
        $response->assertSessionHasErrors([
            'photo' => 'Unable to save the profile photo. Please try again.',
        ]);
        $response->assertSessionMissing('status');
        $this->assertSame('profile-photos/current.jpg', $user->fresh()->profile_photo);
        $publicDisk->assertExists('profile-photos/current.jpg');
    }

    public function test_successful_replacement_saves_new_photo_before_removing_old_managed_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/current.jpg', 'existing-photo');

        $user = User::factory()->create([
            'role' => 'teacher',
            'profile_photo' => 'profile-photos/current.jpg',
        ]);
        $this->actingAs($user);

        $response = $this->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('replacement.jpg'),
        ]);

        $response->assertRedirect(route('settings.profile'));
        $response->assertSessionHas('status', 'Profile photo updated successfully.');

        $newPhotoPath = $user->fresh()->profile_photo;
        $this->assertNotSame('profile-photos/current.jpg', $newPhotoPath);
        $this->assertStringStartsWith('profile-photos/', $newPhotoPath);
        Storage::disk('public')->assertExists($newPhotoPath);
        Storage::disk('public')->assertMissing('profile-photos/current.jpg');
    }

    public function test_replacing_avatar_does_not_delete_a_photo_outside_the_managed_directory(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/shared.jpg', 'unrelated-photo');

        $user = User::factory()->create([
            'role' => 'teacher',
            'profile_photo' => 'avatars/shared.jpg',
        ]);
        $this->actingAs($user);

        $response = $this->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('replacement.jpg'),
        ]);

        $response->assertRedirect(route('settings.profile'));
        Storage::disk('public')->assertExists('avatars/shared.jpg');
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
