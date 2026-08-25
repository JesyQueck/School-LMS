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
        $response->assertSee('Greenfield Academy');
    }

    public function test_about_page_is_accessible(): void
    {
        $response = $this->get('/about');

        $response->assertOk();
        $response->assertSee('About Greenfield Academy');
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
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        Announcement::create([
            'title' => 'School Closure',
            'body' => 'School will be closed tomorrow.',
            'target_role' => 'all',
            'created_by' => $adminUser->id,
        ]);

        Announcement::create([
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
        $pages = [
            '/',
            '/about',
            '/contact',
            '/admissions',
            '/announcements',
        ];

        foreach ($pages as $page) {
            $response = $this->get($page);

            $response->assertOk();
        }
    }

    public function test_footer_uses_config_school_values(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(config('school.name', 'Greenfield Academy'));
        $response->assertSee(config('school.address', '123 Education Lane, Victoria Island, Lagos'));
        $response->assertSee(config('school.phone', '+234 800 000 0000'));
        $response->assertSee(config('school.email', 'info@greenfieldacademy.edu'));
    }

    public function test_footer_does_not_contain_stale_address(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Greenfield City, State 10001');
    }

    public function test_public_announcements_page_shows_announcements_with_show_on_website(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        Announcement::create([
            'title' => 'Staff Meeting',
            'body' => 'Teachers only notice.',
            'target_role' => 'teacher',
            'show_on_website' => true,
            'created_by' => $adminUser->id,
        ]);

        Announcement::create([
            'title' => 'Student Exam',
            'body' => 'Students only notice.',
            'target_role' => 'student',
            'show_on_website' => false,
            'created_by' => $adminUser->id,
        ]);

        $response = $this->get('/announcements');

        $response->assertOk();
        $response->assertSee('Staff Meeting');
        $response->assertDontSee('Student Exam');
    }

    public function test_public_homepage_shows_announcements_with_show_on_website(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        Announcement::create([
            'title' => 'Public Holiday',
            'body' => 'School closed.',
            'target_role' => 'all',
            'show_on_website' => true,
            'created_by' => $adminUser->id,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Public Holiday');
    }

    public function test_announcement_show_on_website_defaults_to_false(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $announcement = Announcement::create([
            'title' => 'Teacher Only',
            'body' => 'Internal notice.',
            'target_role' => 'teacher',
            'created_by' => $adminUser->id,
        ]);

        $this->assertFalse($announcement->fresh()->show_on_website);
    }

    public function test_admin_can_create_announcement_with_show_on_website(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $response = $this->post(route('admin.announcements.store'), [
            'title' => 'Website Feature',
            'body' => 'Check out our new website.',
            'target_role' => 'student',
            'show_on_website' => true,
        ]);

        $response->assertRedirect(route('admin.announcements'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Website Feature',
            'target_role' => 'student',
            'show_on_website' => true,
        ]);
    }
}
