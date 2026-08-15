<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase;

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
