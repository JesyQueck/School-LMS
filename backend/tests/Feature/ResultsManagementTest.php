<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_submit_and_lock_results(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

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
            'is_compulsory' => true,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM202',
        ]);

        $response = $this->post('/admin/results', [
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => '15.50',
            'exam_score' => '45.50',
        ]);

        $response->assertRedirect('/admin/results');
        $this->assertDatabaseHas('results', ['student_id' => $student->id, 'class_subject_id' => $classSubject->id, 'term_id' => $term->id]);

        $result = \App\Models\Result::where('student_id', $student->id)->firstOrFail();

        // The Nigerian 9-tier grading scale should be applied (ca 15.50 + exam 45.50 = 61.00 => C4).
        $this->assertSame(61.00, (float) $result->total);
        $this->assertSame('C4', $result->grade);
        $this->assertSame('Credit', $result->remark);
        $lockResponse = $this->post('/admin/results/' . $result->id . '/lock', []);
        $lockResponse->assertRedirect('/admin/results');
        $this->assertDatabaseHas('results', ['id' => $result->id, 'is_locked' => true]);
    }
}
