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

class CsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_students_csv(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM901',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->actingAs($adminUser);

        $response = $this->get('/admin/students/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="students.csv"');
    }

    public function test_admin_can_export_results_csv(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
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
        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM902',
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

        $this->actingAs($adminUser);

        $response = $this->get('/admin/results/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="results.csv"');
    }
}
