<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_publish_a_report_card(): void
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
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM303',
        ]);

        $response = $this->post('/admin/report-cards', [
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_teacher_remark' => 'Promoted',
            'principal_remark' => 'Keep it up',
            'position_in_class' => 2,
            'total_students_in_class' => 30,
            'next_term_begins' => '2027-01-10',
        ]);

        $response->assertRedirect('/admin/report-cards');
        $this->assertDatabaseHas('report_cards', ['student_id' => $student->id, 'term_id' => $term->id, 'is_published' => false]);

        $reportCard = ReportCard::where('student_id', $student->id)->firstOrFail();
        $publishResponse = $this->post('/admin/report-cards/' . $reportCard->id . '/publish', []);
        $publishResponse->assertRedirect('/admin/report-cards');
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id, 'is_published' => true]);
    }
}
