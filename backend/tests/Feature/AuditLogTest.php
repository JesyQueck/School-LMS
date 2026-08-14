<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_class_and_audit_log_is_recorded(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($adminUser);

        $response = $this->post('/admin/classes', [
            'name' => 'JSS 2',
        ]);

        $response->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $adminUser->id,
            'action' => 'class.created',
            'target_model' => SchoolClass::class,
        ]);
    }

    public function test_admin_can_create_student_and_audit_log_is_recorded(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $class = SchoolClass::create([
            'name' => 'JSS 1',
        ]);

        $this->actingAs($adminUser);

        $response = $this->post('/admin/students', [
            'name' => 'Jane Student',
            'email' => 'jane.student@example.com',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'female',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin/students');

        $student = Student::whereHas('user', function ($query) {
            $query->where('email', 'jane.student@example.com');
        })->first();

        $this->assertNotNull($student);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $adminUser->id,
            'action' => 'student.created',
            'target_model' => Student::class,
            'target_id' => $student->id,
        ]);
    }

    public function test_admin_can_publish_report_card_and_audit_log_is_recorded(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
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

        $class = SchoolClass::create([
            'name' => 'JSS 1',
        ]);

        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM704',
        ]);

        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'grade' => 'A1',
            'is_locked' => true,
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'is_published' => false,
        ]);

        $this->actingAs($adminUser);

        $response = $this->post(
            "/admin/report-cards/{$reportCard->id}/publish"
        );

        $response->assertRedirect('/admin/report-cards');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $adminUser->id,
            'action' => 'report_card.published',
            'target_model' => ReportCard::class,
            'target_id' => $reportCard->id,
        ]);
    }
}
