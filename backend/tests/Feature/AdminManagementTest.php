<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ParentProfile;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
    }

    protected function validStudentPayload(SchoolClass $class, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Student',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'admission_no' => 'GRA-TEST-001',
            'class_id' => $class->id,
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'parent_email' => 'parent@example.com',
            'parent_name' => 'Parent Name',
            'parent_phone' => '08012345678',
            'emergency_1_name' => 'Emergency Contact',
            'emergency_1_relationship' => 'Uncle',
            'emergency_1_phone' => '08098765432',
        ], $overrides);
    }

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

    protected function makeParent(string $name = 'Parent', string $email = 'parent@example.com'): ParentProfile
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => 'parent',
        ]);

        return ParentProfile::create([
            'user_id' => $user->id,
            'occupation' => 'Teacher',
            'phone' => '08012345678',
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

        $session = AcademicSession::where('is_current', true)->first();

        $response = $this->post('/admin/students', [
            'name' => 'Jane Student',
            'first_name' => 'Jane',
            'last_name' => 'Student',
            'email' => 'jane.student@example.com',
            'parent_email' => 'parent@example.com',
            'parent_name' => 'Parent Name',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-0001',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'female',
            'admission_date' => '2024-09-01',
            'academic_session_id' => $session->id,
            'student_type' => 'new',
            'emergency_1_name' => 'Emergency Contact',
            'emergency_1_relationship' => 'Uncle',
            'emergency_1_phone' => '08098765432',
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
            'admission_no' => 'GRA-0001',
        ]);
    }

    public function test_admin_can_create_student_without_email_only_parent_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', [
            'name' => 'No Email Student',
            'first_name' => 'No',
            'last_name' => 'Email',
            'parent_email' => 'parent.noemail@example.com',
            'parent_name' => 'Parent Name',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-0099',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'emergency_1_name' => 'Emergency Contact',
            'emergency_1_relationship' => 'Uncle',
            'emergency_1_phone' => '08098765432',
        ]);

        $response->assertRedirect('/admin/students');

        $this->assertDatabaseHas('students', [
            'admission_no' => 'GRA-0099',
        ]);

        $student = Student::where('admission_no', 'GRA-0099')->first();
        $this->assertNotNull($student);
        $this->assertDatabaseHas('users', [
            'id' => $student->user_id,
            'name' => 'No Email Student',
            'role' => 'student',
        ]);
        $this->assertStringNotContainsString('@example.com', User::find($student->user_id)->email);
        $this->assertStringContainsString('@placeholder.local', User::find($student->user_id)->email);
    }

    public function test_parent_email_is_required_for_student_creation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'parent_email' => '',
        ]));

        $response->assertSessionHasErrors(['parent_email']);
        $this->assertDatabaseMissing('students', [
            'admission_no' => 'GRA-TEST-001',
        ]);
    }

    public function test_admin_can_create_student_with_admission_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'Alice Student',
            'first_name' => 'Alice',
            'last_name' => 'Student',
            'email' => 'alice.student@example.com',
            'parent_email' => 'parent.alice@example.com',
            'admission_no' => 'GRA-1001',
        ]));

        $response->assertRedirect('/admin/students');

        $this->assertDatabaseHas('students', [
            'admission_no' => 'GRA-1001',
        ]);
    }

    public function test_admission_number_is_stored_exactly_as_entered(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'Bob Student',
            'first_name' => 'Bob',
            'last_name' => 'Student',
            'email' => 'bob.student@example.com',
            'parent_email' => 'parent.bob@example.com',
            'admission_no' => 'GRA-ABC-2024',
        ]));

        $student = Student::whereHas('user', fn ($q) => $q->where('name', 'Bob Student'))->first();

        $this->assertNotNull($student);
        $this->assertSame('GRA-ABC-2024', $student->admission_no);
    }

    public function test_admission_number_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'Carol Student',
            'admission_no' => '',
        ]));

        $response->assertSessionHasErrors(['admission_no']);
    }

    public function test_duplicate_admission_number_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        User::factory()->create(['role' => 'student', 'name' => 'First Student', 'email' => 'first@example.com']);
        $user1 = User::where('email', 'first@example.com')->first();
        Student::create(['user_id' => $user1->id, 'admission_no' => 'GRA-DUP-001', 'class_id' => $class->id, 'status' => 'active']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'Second Student',
            'first_name' => 'Second',
            'last_name' => 'Student',
            'email' => 'second@example.com',
            'parent_email' => 'parent.second@example.com',
            'admission_no' => 'GRA-DUP-001',
        ]));

        $response->assertSessionHasErrors(['admission_no']);
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

        $session = AcademicSession::where('is_current', true)->first();

        $response = $this->post('/admin/students', [
            'name' => 'John Student',
            'first_name' => 'John',
            'last_name' => 'Student',
            'email' => 'john.student@example.com',
            'admission_no' => 'GRA-0002',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'admission_date' => '2024-09-01',
            'academic_session_id' => $session->id,
            'student_type' => 'new',
            'parent_email' => 'parent@example.com',
            'parent_name' => 'Mr. Student',
            'parent_phone' => '08012345678',
            'parent_occupation' => 'Engineer',
            'emergency_1_name' => 'Emergency Contact',
            'emergency_1_relationship' => 'Uncle',
            'emergency_1_phone' => '08098765432',
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

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'John Student',
            'admission_no' => 'GRA-0005',
            'parent_name' => str_repeat('A', 300),
        ]));

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

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'Test Student',
            'parent_email' => 'parent@test.example.com',
            'admission_no' => 'GRA-0003',
        ]));

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

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'parent_email' => 'parent.john@example.com',
            'admission_no' => 'GRA-0004',
        ]));

        $response->assertRedirect('/admin/students');

        $student = Student::where('admission_no', 'GRA-0004')->first();
        $this->assertNotNull($student);
        $this->assertSame('John', $student->first_name);
        $this->assertSame('Doe', $student->last_name);
        $this->assertSame('John Doe', $student->full_name);
    }

    public function test_admin_can_edit_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $class2 = SchoolClass::create(['name' => 'JSS 2']);
        $parent = $this->makeParent('Jane Parent', 'parent.test@example.com');
        $studentUser = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'test.student@example.com',
            'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'GRA-TEST-001',
            'class_id' => $class->id,
        ]);
        $student->parents()->attach($parent->id);

        $response = $this->get(route('admin.students.edit', $student));

        $response->assertOk();
        $response->assertSee('Edit Student');
        $response->assertSee('Test Student');
    }

    public function test_admin_can_update_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $class2 = SchoolClass::create(['name' => 'JSS 2']);
        $parent = $this->makeParent('Jane Parent', 'parent.test@example.com');
        $studentUser = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'test.student@example.com',
            'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'GRA-TEST-002',
            'class_id' => $class->id,
        ]);
        $student->parents()->attach($parent->id);

        $response = $this->put(route('admin.students.update', $student), [
            'name' => 'Updated Student',
            'email' => 'updated@example.com',
            'parent_email' => 'parent.updated@example.com',
            'parent_name' => 'Updated Parent',
            'parent_phone' => '08011112222',
            'parent_occupation' => 'Doctor',
            'admission_no' => 'GRA-TEST-002',
            'class_id' => $class2->id,
            'first_name' => 'Updated',
            'last_name' => 'Student',
            'date_of_birth' => '2012-03-15',
            'gender' => 'female',
            'admission_date' => '2024-09-01',
            'academic_session_id' => AcademicSession::where('is_current', true)->value('id'),
            'student_type' => 'returning',
        ]);

        $response->assertRedirect(route('admin.students'));
        $this->assertDatabaseHas('users', [
            'id' => $student->user_id,
            'name' => 'Updated Student',
            'email' => 'updated@example.com',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Updated',
            'last_name' => 'Student',
            'admission_no' => 'GRA-TEST-002',
            'class_id' => $class2->id,
            'gender' => 'female',
        ]);
    }

    public function test_admin_can_update_student_without_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $parent = $this->makeParent('Jane Parent', 'parent.noemail@example.com');
        $studentUser = User::factory()->create([
            'name' => 'No Email Student',
            'email' => 'noemail.student@example.com',
            'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'GRA-TEST-003',
            'class_id' => $class->id,
        ]);

        $response = $this->put(route('admin.students.update', $student), [
            'name' => 'No Email Student',
            'email' => '',
            'parent_email' => 'parent.noemail@example.com',
            'parent_name' => 'Jane Parent',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-TEST-003',
            'class_id' => $class->id,
            'first_name' => 'No',
            'last_name' => 'Email',
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'admission_date' => '2024-09-01',
            'academic_session_id' => AcademicSession::where('is_current', true)->value('id'),
            'student_type' => 'new',
        ]);

        $response->assertRedirect(route('admin.students'));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'admission_no' => 'GRA-TEST-003',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id,
            'email' => 'noemail.student@example.com',
        ]);
    }

    public function test_parent_email_is_required_on_student_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $studentUser = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'test.student@example.com',
            'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'GRA-TEST-004',
            'class_id' => $class->id,
        ]);

        $response = $this->put(route('admin.students.update', $student), [
            'name' => 'Test Student',
            'parent_email' => '',
            'parent_name' => 'Test Parent',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-TEST-004',
            'class_id' => $class->id,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'admission_date' => '2024-09-01',
            'academic_session_id' => AcademicSession::where('is_current', true)->value('id'),
            'student_type' => 'new',
        ]);

        $response->assertSessionHasErrors(['parent_email']);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'admission_no' => 'GRA-TEST-004',
        ]);
    }

    public function test_student_update_validates_unique_admission_no(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $parent = $this->makeParent('Jane Parent', 'parent.unique@example.com');
        $studentUser1 = User::factory()->create([
            'name' => 'Student One',
            'email' => 'one@test.com',
            'role' => 'student',
        ]);
        $studentUser2 = User::factory()->create([
            'name' => 'Student Two',
            'email' => 'two@test.com',
            'role' => 'student',
        ]);
        $student1 = Student::create([
            'user_id' => $studentUser1->id,
            'admission_no' => 'GRA-DUP-001',
            'class_id' => $class->id,
        ]);
        $student2 = Student::create([
            'user_id' => $studentUser2->id,
            'admission_no' => 'GRA-DUP-002',
            'class_id' => $class->id,
        ]);

        $response = $this->put(route('admin.students.update', $student1), [
            'name' => 'Student One',
            'first_name' => 'Student',
            'last_name' => 'One',
            'parent_email' => 'parent.unique@example.com',
            'parent_name' => 'Jane Parent',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-DUP-002',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'admission_date' => '2024-09-01',
            'academic_session_id' => AcademicSession::where('is_current', true)->value('id'),
            'student_type' => 'new',
        ]);

        $response->assertSessionHasErrors(['admission_no']);
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

    public function test_admin_enroll_page_is_accessible(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.students.enroll'));

        $response->assertOk();
        $response->assertSee('Enroll Student');
        $response->assertSee('Student Information');
        $response->assertSee('Parent / Guardian');
        $response->assertSee('Emergency Contact');
        $response->assertSee('Documents');
    }

    public function test_admin_enroll_student_creates_complete_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $session = AcademicSession::where('is_current', true)->first();

        $response = $this->post('/admin/students', [
            'name' => 'Complete Student',
            'first_name' => 'Complete',
            'last_name' => 'Student',
            'middle_name' => 'Middle',
            'admission_no' => 'GRA-FULL-001',
            'class_id' => $class->id,
            'date_of_birth' => '2010-05-15',
            'gender' => 'female',
            'nationality' => 'Nigerian',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'religion' => 'Christianity',
            'home_address' => '123 Main Street',
            'city' => 'Lagos',
            'state' => 'Lagos',
            'admission_date' => '2024-09-01',
            'academic_session_id' => $session->id,
            'student_type' => 'new',
            'previous_school' => 'ABC Primary School',
            'previous_school_address' => '456 School Road',
            'previous_class' => 'Primary 5',
            'previous_year_attended' => '2024',
            'parent_email' => 'parent.complete@example.com',
            'parent_name' => 'Mrs. Complete',
            'parent_phone' => '08012345678',
            'parent_whatsapp' => '08012345678',
            'parent_address' => '123 Main Street, Lagos',
            'parent_occupation' => 'Engineer',
            'father_name' => 'Mr. Father',
            'father_phone' => '08011111111',
            'father_email' => 'father@example.com',
            'mother_name' => 'Mrs. Mother',
            'mother_phone' => '08022222222',
            'emergency_1_name' => 'Emergency One',
            'emergency_1_relationship' => 'Uncle',
            'emergency_1_phone' => '08098765432',
            'emergency_2_name' => 'Emergency Two',
            'emergency_2_relationship' => 'Aunt',
            'emergency_2_phone' => '08098765433',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin/students');

        $student = Student::where('admission_no', 'GRA-FULL-001')->first();
        $this->assertNotNull($student);
        $this->assertSame('Complete', $student->first_name);
        $this->assertSame('Middle', $student->middle_name);
        $this->assertSame('Student', $student->last_name);
        $this->assertSame('female', $student->gender);
        $this->assertSame('Nigerian', $student->nationality);
        $this->assertSame('Lagos', $student->state_of_origin);
        $this->assertSame('Ikeja', $student->lga);
        $this->assertSame('Christianity', $student->religion);
        $this->assertSame('123 Main Street', $student->home_address);
        $this->assertSame('Lagos', $student->city);
        $this->assertSame('Lagos', $student->state);
        $this->assertEquals($session->id, $student->academic_session_id);
        $this->assertSame('new', $student->student_type);
        $this->assertSame('ABC Primary School', $student->previous_school);
        $this->assertSame('Primary 5', $student->previous_class);
        $this->assertSame('2024', $student->previous_year_attended);

        $this->assertDatabaseHas('student_contacts', [
            'student_id' => $student->id,
            'type' => 'father',
            'full_name' => 'Mr. Father',
            'phone' => '08011111111',
            'email' => 'father@example.com',
        ]);
        $this->assertDatabaseHas('student_contacts', [
            'student_id' => $student->id,
            'type' => 'mother',
            'full_name' => 'Mrs. Mother',
            'phone' => '08022222222',
        ]);

        $this->assertDatabaseHas('student_emergency_contacts', [
            'student_id' => $student->id,
            'name' => 'Emergency One',
            'relationship' => 'Uncle',
            'phone' => '08098765432',
            'is_primary' => true,
        ]);
        $this->assertDatabaseHas('student_emergency_contacts', [
            'student_id' => $student->id,
            'name' => 'Emergency Two',
            'relationship' => 'Aunt',
            'is_primary' => false,
        ]);
    }

    public function test_admin_enroll_requires_date_of_birth(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'date_of_birth' => '',
        ]));

        $response->assertSessionHasErrors(['date_of_birth']);
    }

    public function test_admin_enroll_requires_gender(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'gender' => '',
        ]));

        $response->assertSessionHasErrors(['gender']);
    }

    public function test_admin_enroll_requires_parent_phone(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'parent_phone' => '',
        ]));

        $response->assertSessionHasErrors(['parent_phone']);
    }

    public function test_admin_enroll_requires_emergency_contact(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'emergency_1_name' => '',
        ]));

        $response->assertSessionHasErrors(['emergency_1_name']);
    }

    public function test_admin_enroll_invalid_gender_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'gender' => 'invalid',
        ]));

        $response->assertSessionHasErrors(['gender']);
    }

    public function test_admin_enroll_invalid_student_type_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'student_type' => 'invalid',
        ]));

        $response->assertSessionHasErrors(['student_type']);
    }

    public function test_existing_parent_linked_without_duplicate_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $existingParent = $this->makeParent('Existing Parent', 'existing.parent@example.com');

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'parent_email' => 'existing.parent@example.com',
            'name' => 'Child Of Existing',
            'first_name' => 'Child',
            'last_name' => 'Of Existing',
            'admission_no' => 'GRA-EXIST-001',
        ]));

        $response->assertRedirect('/admin/students');

        $this->assertCount(1, User::where('email', 'existing.parent@example.com')->get());
        $parentCount = ParentProfile::whereHas('user', fn ($q) => $q->where('email', 'existing.parent@example.com'))->count();
        $this->assertSame(1, $parentCount);

        $student = Student::where('admission_no', 'GRA-EXIST-001')->first();
        $this->assertNotNull($student);
        $this->assertTrue($student->parents->contains($existingParent->id));
    }

    public function test_academic_session_is_stored_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $session = AcademicSession::where('is_current', true)->first();

        $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'Session Student',
            'admission_no' => 'GRA-SESS-001',
            'academic_session_id' => $session->id,
            'student_type' => 'transfer',
        ]));

        $student = Student::where('admission_no', 'GRA-SESS-001')->first();
        $this->assertNotNull($student);
        $this->assertEquals($session->id, $student->academic_session_id);
        $this->assertSame('transfer', $student->student_type);
    }

    public function test_student_assigned_to_selected_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        SchoolClass::create(['name' => 'JSS 1']);
        $targetClass = SchoolClass::create(['name' => 'JSS 2']);

        $response = $this->post('/admin/students', $this->validStudentPayload($targetClass, [
            'name' => 'Class Test',
            'admission_no' => 'GRA-CLASS-001',
        ]));

        $response->assertRedirect('/admin/students');

        $student = Student::where('admission_no', 'GRA-CLASS-001')->first();
        $this->assertNotNull($student);
        $this->assertSame($targetClass->id, $student->class_id);
    }

    public function test_optional_fields_can_be_omitted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'email' => null,
        ]));

        $response->assertRedirect('/admin/students');

        $student = Student::where('admission_no', 'GRA-TEST-001')->first();
        $this->assertNotNull($student);
        $this->assertNull($student->nationality);
        $this->assertNull($student->previous_school);
        $this->assertNull($student->home_address);
    }

    public function test_non_admin_user_cannot_enroll_student(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacherUser);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class));

        $response->assertForbidden();
        $this->assertDatabaseMissing('students', ['admission_no' => 'GRA-TEST-001']);
    }

    public function test_student_portal_still_works_after_enrollment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $this->post('/admin/students', $this->validStudentPayload($class, [
            'name' => 'Portal Test',
            'first_name' => 'Portal',
            'last_name' => 'Test',
            'email' => 'portal.test@example.com',
            'admission_no' => 'GRA-PORTAL-001',
        ]));

        $student = Student::where('admission_no', 'GRA-PORTAL-001')->first();
        $this->assertNotNull($student);

        $studentUser = $student->user;

        $this->actingAs($studentUser);
        $response = $this->get('/student/dashboard');
        $response->assertOk();
    }

    public function test_document_uploads_are_stored(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $response = $this->post('/admin/students', $this->validStudentPayload($class, [
            'admission_no' => 'GRA-DOC-001',
        ]) + [
            'document_birth_certificate' => UploadedFile::fake()->create('birth_cert.pdf', 100, 'application/pdf'),
            'document_transfer_certificate' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect('/admin/students');

        $student = Student::where('admission_no', 'GRA-DOC-001')->first();
        $this->assertNotNull($student);

        $this->assertDatabaseHas('student_documents', [
            'student_id' => $student->id,
            'type' => 'birth_certificate',
        ]);
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $student->id,
            'type' => 'transfer_certificate',
        ]);
    }
}
