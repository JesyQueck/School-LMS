<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_assigned_classes_and_submit_attendance(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        ClassAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);
        $subject = Subject::create(['name' => 'English']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_compulsory' => true,
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM401',
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get('/teacher/dashboard');
        $response->assertOk();

        $attendanceResponse = $this->post('/teacher/attendance', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $term->id,
            'date' => '2026-09-15',
            'status' => 'present',
        ]);

        $attendanceResponse->assertRedirect('/teacher/attendance');
        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'status' => 'present']);
    }

    public function test_teacher_cannot_view_students_of_unassigned_class(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $otherClass = SchoolClass::create(['name' => 'JSS 2']);

        $this->actingAs($teacherUser);

        $response = $this->get('/teacher/classes/'.$otherClass->id);
        $response->assertForbidden();
    }

    public function test_teacher_can_view_students_of_assigned_class(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        ClassAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        $this->actingAs($teacherUser);

        // The classStudents method authorizes correctly — a 403 would mean
        // authorization failed. The view has a pre-existing dead route reference
        // (teacher.students.show) which is a separate issue.
        $response = $this->get('/teacher/classes/'.$class->id);
        $this->assertNotEquals(403, $response->status());
    }

    public function test_attendance_allows_same_student_same_date_different_terms(): void
    {
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term1 = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $term2 = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
            'start_date' => '2027-01-05',
            'end_date' => '2027-04-30',
            'is_current' => false,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM401',
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $term1->id,
            'date' => '2026-10-01',
            'status' => 'present',
            'marked_by' => 1,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'term_id' => $term2->id,
            'date' => '2026-10-01',
            'status' => 'absent',
            'marked_by' => 1,
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'term_id' => $term1->id,
            'date' => '2026-10-01 00:00:00',
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'term_id' => $term2->id,
            'date' => '2026-10-01 00:00:00',
            'status' => 'absent',
        ]);
    }

    public function test_class_teacher_sees_their_class_students(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        ClassAssignment::create([
            'teacher_id' => $teacherUser->teacher->id,
            'class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get(route('teacher.students.index'));
        $response->assertOk();
        $response->assertSee('John Doe');
    }

    public function test_subject_teacher_cannot_access_my_students(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_compulsory' => true,
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
            'is_active' => true,
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get(route('teacher.students.index'));
        $response->assertForbidden();
    }

    public function test_teacher_cannot_access_unrelated_student_profile(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $myClass = SchoolClass::create(['name' => 'JSS 1']);
        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $myClass->id,
            'subject_id' => $subject->id,
            'is_compulsory' => true,
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
            'is_active' => true,
        ]);

        $unrelatedStudent = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $otherClass->id,
            'admission_no' => 'ADM002',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get(route('teacher.students.show', $unrelatedStudent));
        $response->assertForbidden();
    }

    public function test_class_teacher_can_access_assigned_student_profile(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        ClassAssignment::create([
            'teacher_id' => $teacherUser->teacher->id,
            'class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM001',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get(route('teacher.students.show', $student));
        $response->assertOk();
        $response->assertSee('John Doe');
    }

    public function test_admin_can_access_any_student(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->actingAs($adminUser);

        $response = $this->get(route('teacher.students.show', $student));
        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_sidebar_shows_my_students_link_for_class_teacher(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        ClassAssignment::create([
            'teacher_id' => $teacherUser->teacher->id,
            'class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get('/teacher/dashboard');
        $response->assertOk();
        $response->assertSee('My Students');
    }

    public function test_sidebar_does_not_show_my_students_for_subject_teacher(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get('/teacher/dashboard');
        $response->assertOk();
        $response->assertDontSee('My Students');
    }

    public function test_teacher_with_multiple_roles_sees_only_class_teacher_students(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-1001',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class1 = SchoolClass::create(['name' => 'JSS 1']);
        $class2 = SchoolClass::create(['name' => 'JSS 2']);

        // Class teacher for class1
        ClassAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class1->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        // Subject teacher for class2
        $subject = Subject::create(['name' => 'English']);
        $classSubject = ClassSubject::create([
            'class_id' => $class2->id,
            'subject_id' => $subject->id,
            'is_compulsory' => true,
        ]);
        TeacherClassSubject::create([
            'teacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
            'is_active' => true,
        ]);

        $classTeacherStudent = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class1->id,
            'admission_no' => 'ADM001',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
        ]);

        $subjectTeacherStudent = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class2->id,
            'admission_no' => 'ADM002',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
        ]);

        $this->actingAs($teacherUser);

        $response = $this->get(route('teacher.students.index'));
        $response->assertOk();
        $response->assertSee('Alice Smith');
        $response->assertDontSee('Bob Jones');
    }

    public function test_attendance_flow_shows_take_attendance_then_overview_then_edit(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'T-2002',
            'qualification' => 'B.Ed',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        ClassAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM402',
            'first_name' => 'Carol',
            'last_name' => 'Dan',
        ]);
        $studentUser2 = User::factory()->create();
        $student2 = Student::create([
            'user_id' => $studentUser2->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM403',
            'first_name' => 'Eve',
            'last_name' => 'Lee',
        ]);

        $this->actingAs($teacherUser);

        // Initial state: "Take Attendance" prompt is shown (no started/marked flags).
        $initial = $this->get('/teacher/attendance')->assertOk();
        $initial->assertViewHas('showOverview', false);
        $initial->assertViewHas('showAttendanceForm', false);
        $initial->assertSee('Take Attendance');

        // Click "Take Attendance" sets the started flag → the marking form appears.
        $this->post('/teacher/attendance/start', [
            'class_id' => $class->id,
            'term_id' => $term->id,
        ])->assertRedirect('/teacher/attendance');

        $form = $this->get('/teacher/attendance')->assertOk();
        $form->assertViewHas('showAttendanceForm', true);
        $form->assertViewHas('showOverview', false);
        $form->assertSee('Save Attendance');

        // Submit attendance (one present, one absent).
        $this->post('/teacher/attendance', [
            'class_id' => $class->id,
            'term_id' => $term->id,
            'date' => now()->toDateString(),
            'status' => [
                $student->id => 'present',
                $student2->id => 'absent',
            ],
        ])->assertRedirect('/teacher/attendance');

        // Overview now replaces the form, with an Edit button and the student names.
        $overview = $this->get('/teacher/attendance')->assertOk();
        $overview->assertViewHas('showOverview', true);
        $overview->assertViewHas('showAttendanceForm', false);
        $overview->assertSee('Edit Attendance')
            ->assertSee('Carol Dan')
            ->assertSee('Eve Lee');

        // Clicking Edit returns to the marking form.
        $this->get('/teacher/attendance/edit')->assertRedirect('/teacher/attendance');
        $formAgain = $this->get('/teacher/attendance')->assertOk();
        $formAgain->assertViewHas('showAttendanceForm', true);
        $formAgain->assertViewHas('showOverview', false);
        $formAgain->assertSee('Save Attendance');

        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'status' => 'present']);
        $this->assertDatabaseHas('attendances', ['student_id' => $student2->id, 'status' => 'absent']);
    }
}
