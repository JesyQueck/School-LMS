<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_announcements_index(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/announcements');

        $response->assertStatus(200);
        $response->assertViewIs('admin.announcements.index');
    }

    public function test_non_admin_cannot_access_announcements_index(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get('/admin/announcements');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_announcement_create_page(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/announcements/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.announcements.create');
    }

    public function test_announcement_creation_redirects_to_announcements_index(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $response = $this->post('/admin/announcements', [
            'title' => 'School Holiday',
            'body' => 'The school will be closed for holidays.',
            'target_role' => 'all',
        ]);

        $response->assertRedirect('/admin/announcements');
        $this->assertDatabaseHas('announcements', [
            'title' => 'School Holiday',
            'target_role' => 'all',
        ]);
    }

    public function test_announcement_with_body_exceeding_10000_characters_is_rejected(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $response = $this->post('/admin/announcements', [
            'title' => 'Long Announcement',
            'body' => str_repeat('a', 10001),
            'target_role' => 'all',
        ]);

        $response->assertSessionHasErrors('body');
        $this->assertDatabaseMissing('announcements', [
            'title' => 'Long Announcement',
        ]);
    }

    public function test_announcement_with_body_at_exact_10000_characters_is_accepted(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $response = $this->post('/admin/announcements', [
            'title' => 'Max Length Announcement',
            'body' => str_repeat('a', 10000),
            'target_role' => 'all',
        ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'Max Length Announcement',
            'body' => str_repeat('a', 10000),
            'target_role' => 'all',
        ]);
    }

    public function test_announcement_with_valid_body_is_accepted(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $response = $this->post('/admin/announcements', [
            'title' => 'School Holiday',
            'body' => 'The school will be closed for holidays.',
            'target_role' => 'all',
        ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'School Holiday',
            'target_role' => 'all',
        ]);
    }
}
