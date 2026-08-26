<?php

namespace Tests\Feature;

use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AuthenticatedLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function activeNavLabel(string $content): ?string
    {
        if (preg_match('/<a [^>]*aria-current="page"[^>]*>(.*?)<\/a>/s', $content, $matches)) {
            if (preg_match('/<span[^>]*>([^<]*)<\/span>/', $matches[1], $span)) {
                return trim($span[1]);
            }
        }

        return null;
    }

    public function test_admin_receives_admin_navigation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        $response->assertOk();
        // Admin-only navigation items
        foreach (['Dashboard', 'Students', 'Teachers', 'Classes', 'Academic Structure', 'Subjects', 'Report Cards', 'Timetable', 'Fees / Payments', 'Announcements', 'Accounts', 'Audit Logs', 'Profile', 'Change Password', 'Logout'] as $label) {
            $response->assertSee($label);
        }
        // Teacher-only and parent-only sections must not leak into the admin layout
        $response->assertDontSee('My Classes');
        $response->assertDontSee('Children');
    }

    public function test_teacher_receives_teacher_navigation(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'TCH-1001',
            'qualification' => 'B.Ed',
        ]);
        $this->actingAs($teacherUser);

        $response = $this->get('/teacher/dashboard');

        $response->assertOk();
        foreach (['Dashboard', 'My Classes', 'Attendance', 'Results', 'Report Cards', 'Timetable', 'Profile', 'Change Password', 'Logout'] as $label) {
            $response->assertSee($label);
        }
        // Admin-only sections must not leak into the teacher layout
        $response->assertDontSee('Audit Logs');
        $response->assertDontSee('Accounts');
        $response->assertDontSee('Fees / Payments');
        $response->assertDontSee('Children');
    }

    public function test_student_receives_student_navigation(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-001',
            'first_name' => 'Amina',
            'last_name' => 'Bello',
        ]);
        $this->actingAs($studentUser);

        $response = $this->get('/student/dashboard');

        $response->assertOk();
        foreach (['Dashboard', 'Timetable', 'Attendance', 'Report Cards', 'Fees', 'Profile', 'Change Password', 'Logout'] as $label) {
            $response->assertSee($label);
        }
        // Admin/teacher/parent-only sections must not leak into the student layout
        $response->assertDontSee('Audit Logs');
        $response->assertDontSee('Accounts');
        $response->assertDontSee('Fees / Payments');
        $response->assertDontSee('My Classes');
        $response->assertDontSee('Children');
    }

    public function test_parent_receives_parent_navigation(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id]);

        $parentProfile = $parentUser->fresh()->parentProfile;

        $childUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $child = Student::create([
            'user_id' => $childUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-002',
            'first_name' => 'Ahmed',
            'last_name' => 'Bello',
        ]);

        $parentProfile->students()->attach($child->id);

        $this->actingAs($parentUser);

        $response = $this->get('/parent/dashboard');

        $response->assertOk();
        foreach (['Dashboard', 'Children', 'Timetable', 'Profile', 'Change Password', 'Logout'] as $label) {
            $response->assertSee($label);
        }
        // Admin-only sections must not leak into the parent layout
        $response->assertDontSee('Audit Logs');
        $response->assertDontSee('Accounts');
        $response->assertDontSee('Fees / Payments');
        $response->assertDontSee('My Classes');
    }

    public function test_role_isolation_blocks_other_roles_from_admin_area(): void
    {
        // A parent cannot reach the admin area before the admin sidebar would render
        $parent = User::factory()->create(['role' => 'parent']);
        ParentProfile::create(['user_id' => $parent->id]);
        $this->actingAs($parent);
        $this->get('/admin/dashboard')->assertStatus(403);

        // A student cannot reach the teacher area
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);
        $this->get('/teacher/dashboard')->assertStatus(403);

        // A teacher cannot reach the student area
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-9009',
            'qualification' => 'B.Ed',
        ]);
        $this->actingAs($teacherUser);
        $this->get('/student/dashboard')->assertStatus(403);
    }

    public function test_active_navigation_highlights_current_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        $response->assertSee('aria-current="page"', false);
        $this->assertSame('Classes', $this->activeNavLabel($response->getContent()));

        // Nested route still highlights the parent section link
        $editResponse = $this->get('/admin/classes/'.$class->id.'/edit');
        $editResponse->assertSee('aria-current="page"', false);
        $this->assertSame('Classes', $this->activeNavLabel($editResponse->getContent()));
    }

    public function test_school_name_in_sidebar_comes_from_config(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $original = config('school.name');
        try {
            Config::set('school.name', 'CustomBrandSchoolXYZ');
            $response = $this->get('/admin/classes');
            $response->assertSee('CustomBrandSchoolXYZ');
        } finally {
            Config::set('school.name', $original);
        }
    }

    public function test_sidebar_uses_existing_primary_brand_color(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        $response->assertSee('bg-primary-700', false);
    }

    public function test_profile_photo_renders_above_name_in_sidebar(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'profile_photo' => 'profile-photos/photo.jpg',
        ]);
        SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        // The profile photo (img) is used and renders before the user's name
        $response->assertSee('profile-photos/photo.jpg', false);
        $response->assertSeeInOrder(['profile-photos/photo.jpg', 'Admin User']);
    }

    public function test_settings_page_renders_uploader_for_non_students(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher', 'name' => 'Mr. Smith']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'TCH-3003',
            'qualification' => 'B.Ed',
        ]);
        $this->actingAs($teacherUser);

        $response = $this->get(route('settings.profile'));

        $response->assertOk();
        $response->assertSee('Choose photo');
        $response->assertSee('Profile');
    }

    public function test_settings_page_is_read_only_for_students(): void
    {
        $studentUser = User::factory()->create(['role' => 'student', 'name' => 'Amina Bello']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-003',
            'first_name' => 'Amina',
            'last_name' => 'Bello',
        ]);
        $this->actingAs($studentUser);

        $response = $this->get(route('settings.profile'));

        $response->assertOk();
        $response->assertDontSee('Choose photo');
        $response->assertSee('Managed by your administrator');
    }

    public function test_sidebar_renders_school_logo_when_configured(): void
    {
        Config::set('school.logo', 'images/Logo.webp');

        $admin = User::factory()->create(['role' => 'admin']);
        SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        $response->assertSee('images/Logo.webp', false);
    }

    public function test_sidebar_shows_brand_fallback_initials_when_logo_missing(): void
    {
        Config::set('school.logo', 'images/logo.svg');

        $admin = User::factory()->create(['role' => 'admin']);
        SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        // No logo file exists at images/logo.svg, so the school initials fallback renders
        $this->assertStringNotContainsString('<img src="', $response->getContent());
        $response->assertSee(config('school.name', config('app.name', 'Greenfield Academy')));
    }

    public function test_theme_toggle_renders_in_header(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SchoolClass::create(['name' => 'JSS 1']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        $response->assertSee('id="theme-toggle"', false);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_logout_remains_functional(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
