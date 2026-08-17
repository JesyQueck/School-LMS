<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_classes_index_shows_not_assigned_without_literal_span_tags(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        SchoolClass::create(['name' => 'JSS 2']);

        $response = $this->get('/admin/classes');

        $response->assertOk();
        $response->assertSee('Not assigned');
        $response->assertSee('<span class="text-neutral-400">Not assigned</span>', false);
        $response->assertDontSee('&lt;span', false);
    }

    public function test_classes_index_shows_form_teacher_name_when_assigned(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $user = User::factory()->create([
            'name' => 'Jane Teacher',
            'role' => 'teacher',
            'email' => 'jane.teacher@example.com',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'employee_id' => 'TCH001',
            'qualification' => 'B.Ed',
        ]);

        SchoolClass::create([
            'name' => 'JSS 3',
            'form_teacher_id' => $teacher->id,
        ]);

        $response = $this->get('/admin/classes');

        $response->assertOk();
        $response->assertSee('Jane Teacher');
        $response->assertSee('JSS 3');
    }

    public function test_classes_index_page_is_accessible_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/classes');

        $response->assertOk();
        $response->assertSee('Manage school classes');
    }

    public function test_classes_index_is_forbidden_for_non_admin(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $response = $this->get('/admin/classes');

        $response->assertForbidden();
    }

    public function test_admin_can_access_class_edit_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->get(route('admin.classes.edit', $class));

        $response->assertOk();
        $response->assertSee('Edit Class');
        $response->assertSee('JSS 1');
    }

    public function test_admin_can_edit_class_name_and_form_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $user = User::factory()->create([
            'name' => 'New Teacher',
            'role' => 'teacher',
            'email' => 'new.teacher@example.com',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'employee_id' => 'TCH002',
            'qualification' => 'B.Ed',
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->put(route('admin.classes.update', $class), [
            'name' => 'JSS 2',
            'form_teacher_id' => $teacher->id,
        ]);

        $response->assertRedirect(route('admin.classes'));
        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'name' => 'JSS 2',
            'form_teacher_id' => $teacher->id,
        ]);
    }

    public function test_admin_can_correct_class_name_typo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1 ']);

        $response = $this->put(route('admin.classes.update', $class), [
            'name' => 'JSS 1',
        ]);

        $response->assertRedirect(route('admin.classes'));
        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'name' => 'JSS 1',
        ]);
        $this->assertDatabaseMissing('classes', [
            'name' => 'JSS 1 ',
        ]);
    }

    public function test_update_class_validates_name_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->put(route('admin.classes.update', $class), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseHas('classes', ['name' => 'JSS 1']);
    }

    public function test_update_class_validates_name_is_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        SchoolClass::create(['name' => 'JSS 1']);
        $class2 = SchoolClass::create(['name' => 'JSS 2']);

        $response = $this->put(route('admin.classes.update', $class2), [
            'name' => 'JSS 1',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_class_rejects_post_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post(route('admin.classes.update', $class), [
            'name' => 'JSS 2',
        ]);

        $response->assertMethodNotAllowed();
    }

    public function test_non_admin_cannot_access_class_edit_page(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->get(route('admin.classes.edit', $class));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_update_class(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->put(route('admin.classes.update', $class), [
            'name' => 'JSS 2',
        ]);

        $response->assertForbidden();
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
            'password' => 'Password123!',
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

    public function test_admin_can_access_teacher_edit_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->get(route('admin.teachers.edit', $teacher));

        $response->assertOk();
        $response->assertSee('Edit Teacher');
        $response->assertSee('John Smith');
    }

    public function test_admin_can_edit_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->put(route('admin.teachers.update', $teacher), [
            'name' => 'John A. Smith',
            'email' => 'john.smith@example.com',
            'phone' => '08098765432',
            'qualification' => 'M.Ed Mathematics',
            'password' => '',
        ]);

        $response->assertRedirect(route('admin.teachers'));
        $this->assertDatabaseHas('users', [
            'id' => $teacher->user_id,
            'name' => 'John A. Smith',
            'email' => 'john.smith@example.com',
            'phone' => '08098765432',
        ]);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'qualification' => 'M.Ed Mathematics',
        ]);
    }

    public function test_admin_can_correct_teacher_name_typo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('Jon Smith', 'john@example.com');

        $response = $this->put(route('admin.teachers.update', $teacher), [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => '',
        ]);

        $response->assertRedirect(route('admin.teachers'));
        $this->assertDatabaseHas('users', [
            'id' => $teacher->user_id,
            'name' => 'John Smith',
        ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'Jon Smith',
        ]);
    }

    public function test_update_teacher_validates_name_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->put(route('admin.teachers.update', $teacher), [
            'name' => '',
            'email' => 'john@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseHas('users', ['name' => 'John Smith']);
    }

    public function test_update_teacher_validates_email_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');
        $this->makeTeacher('Jane Doe', 'jane@example.com');

        $response = $this->put(route('admin.teachers.update', $teacher), [
            'name' => 'John Smith',
            'email' => 'jane@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_teacher_sends_password_change(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');
        $oldHash = $teacher->user->password;

        $this->put(route('admin.teachers.update', $teacher), [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => 'NewStrongPass1!',
        ]);

        $teacher->user->refresh();
        $this->assertNotEquals($oldHash, $teacher->user->password);
    }

    public function test_admin_can_delete_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->delete(route('admin.teachers.destroy', $teacher));

        $response->assertRedirect(route('admin.teachers'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('users', ['id' => $teacher->user_id]);
    }

    public function test_delete_teacher_prevented_when_assigned_as_form_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');
        SchoolClass::create([
            'name' => 'JSS 1',
            'form_teacher_id' => $teacher->id,
        ]);

        $response = $this->delete(route('admin.teachers.destroy', $teacher));

        $response->assertSessionHasErrors(['teacher'])
            ->assertRedirect(route('admin.teachers'));
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseHas('users', ['id' => $teacher->user_id]);
    }

    public function test_update_teacher_rejects_post_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->post(route('admin.teachers.update', $teacher), [
            'name' => 'John A. Smith',
            'email' => 'john.smith@example.com',
        ]);

        $response->assertMethodNotAllowed();
    }

    public function test_non_admin_cannot_access_teacher_edit_page(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->get(route('admin.teachers.edit', $teacher));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_update_teacher(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->put(route('admin.teachers.update', $teacher), [
            'name' => 'Hacked Name',
            'email' => 'john@example.com',
            'password' => '',
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_teacher(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $teacher = $this->makeTeacher('John Smith', 'john@example.com');

        $response = $this->delete(route('admin.teachers.destroy', $teacher));

        $response->assertForbidden();
    }

    protected function makeTeacher(string $name = 'John Smith', string $email = 'john@example.com'): Teacher
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => 'teacher',
        ]);

        return Teacher::create([
            'user_id' => $user->id,
            'employee_id' => 'TCH-'.Str::random(4),
            'qualification' => 'B.Ed',
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
            'password' => 'Password123!',
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
            'password' => 'Password123!',
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

    public function test_admin_can_unpublish_report_card(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM005',
        ]);
        $term = Term::create([
            'academic_session_id' => AcademicSession::create([
                'name' => '2026/2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-07-31',
                'is_current' => true,
            ])->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $reportCard = ReportCard::where('student_id', $student->id)->first();

        $response = $this->post("/admin/report-cards/{$reportCard->id}/unpublish");

        $response->assertRedirect('/admin/report-cards');
        $this->assertDatabaseHas('report_cards', [
            'id' => $reportCard->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);
    }

    public function test_admin_can_view_report_cards_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/report-cards');
        $response->assertOk();
    }

    public function test_admin_accounts_index_displays_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        User::factory()->create(['role' => 'teacher', 'name' => 'Test Teacher']);

        $response = $this->get('/admin/accounts');
        $response->assertOk();
        $response->assertSee('Test Teacher');
    }

    public function test_admin_can_create_account_via_account_controller(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/accounts', [
            'type' => 'teacher',
            'name' => 'Via Account Controller',
            'email' => 'account.test@example.com',
            'phone' => '08011111111',
            'qualification' => 'B.Ed',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin/accounts/create');
        $this->assertDatabaseHas('users', [
            'email' => 'account.test@example.com',
            'role' => 'teacher',
        ]);
        $this->assertDatabaseHas('teachers', [
            'qualification' => 'B.Ed',
        ]);
    }

    public function test_admin_can_create_parent_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/accounts', [
            'type' => 'parent',
            'name' => 'Parent User',
            'email' => 'parent.user@example.com',
            'phone' => '08022222222',
            'occupation' => 'Doctor',
        ]);

        $response->assertRedirect('/admin/accounts/create');
        $this->assertDatabaseHas('users', [
            'email' => 'parent.user@example.com',
            'role' => 'parent',
        ]);
        $this->assertDatabaseHas('parents', [
            'occupation' => 'Doctor',
        ]);
    }

    public function test_admin_account_store_rejects_invalid_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/accounts', [
            'type' => 'invalid',
            'name' => 'Invalid Type',
            'email' => 'invalid@example.com',
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_admin_can_create_fee_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $term = Term::create([
            'academic_session_id' => AcademicSession::create([
                'name' => '2026/2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-07-31',
                'is_current' => true,
            ])->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $response = $this->post('/admin/finance/fee-types', [
            'name' => 'Exam Fee',
            'amount' => '500.00',
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $response->assertRedirect('/admin/finance');
        $this->assertDatabaseHas('fee_types', [
            'name' => 'Exam Fee',
            'amount' => 500.00,
        ]);
    }

    public function test_admin_finance_index_displays(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/finance');
        $response->assertOk();
    }

    public function test_create_teacher_rejects_weak_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/teachers', [
            'name' => 'Weak Pass',
            'email' => 'weak.pass@example.com',
            'phone' => '08012345678',
            'qualification' => 'B.Sc',
            'password' => 'weak',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', [
            'email' => 'weak.pass@example.com',
        ]);
    }

    public function test_create_teacher_rejects_password_missing_symbol(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/teachers', [
            'name' => 'No Symbol',
            'email' => 'nosymbol@example.com',
            'phone' => '08012345678',
            'qualification' => 'B.Sc',
            'password' => 'Password123',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', [
            'email' => 'nosymbol@example.com',
        ]);
    }

    public function test_create_teacher_accepts_strong_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/teachers', [
            'name' => 'Strong Pass',
            'email' => 'strong.pass@example.com',
            'phone' => '08012345678',
            'qualification' => 'B.Sc',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin/teachers');
        $this->assertDatabaseHas('users', [
            'email' => 'strong.pass@example.com',
        ]);
    }
}
