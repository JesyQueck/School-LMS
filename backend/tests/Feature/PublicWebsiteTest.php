<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Welcome to Our School');
    }

    public function test_about_page_is_accessible(): void
    {
        $response = $this->get('/about');
        $response->assertOk();
        $response->assertSee('About Our School');
    }

    public function test_contact_page_is_accessible(): void
    {
        $response = $this->get('/contact');
        $response->assertOk();
        $response->assertSee('Contact Us');
    }

    public function test_admissions_page_is_accessible(): void
    {
        $response = $this->get('/admissions');
        $response->assertOk();
        $response->assertSee('Admissions');
    }

    public function test_public_announcements_page_shows_all_announcements(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $publicAnnouncement = Announcement::create([
            'title' => 'School Closure',
            'body' => 'School will be closed tomorrow.',
            'target_role' => 'all',
            'created_by' => $adminUser->id,
        ]);

        $teacherAnnouncement = Announcement::create([
            'title' => 'Staff Meeting',
            'body' => 'Teachers only.',
            'target_role' => 'teacher',
            'created_by' => $adminUser->id,
        ]);

        $response = $this->get('/announcements');
        $response->assertOk();
        $response->assertSee('School Closure');
        $response->assertDontSee('Staff Meeting');
    }

    public function test_public_pages_do_not_require_authentication(): void
    {
        $pages = ['/', '/about', '/contact', '/admissions', '/announcements'];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertOk();
        }
    }
}
