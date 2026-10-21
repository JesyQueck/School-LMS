<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_result_metrics_only_count_record_rows_in_the_current_term(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $currentSession = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $pastSession = AcademicSession::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
        ]);
        $currentTerm = Term::create([
            'academic_session_id' => $currentSession->id,
            'name' => 'Current Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);
        $pastTerm = Term::create([
            'academic_session_id' => $pastSession->id,
            'name' => 'Past Term',
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-20',
        ]);
        $class = SchoolClass::create(['name' => 'Dashboard Results Class']);
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-ADMIN-DASH',
        ]);
        $subject = Subject::create(['name' => 'Dashboard Results Subject']);
        $currentClassSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
        $pastSubject = Subject::create(['name' => 'Dashboard Past Results Subject']);
        $pastClassSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $pastSubject->id,
        ]);

        foreach ([
            ['term' => $currentTerm, 'subject' => $currentClassSubject, 'locked' => true],
            ['term' => $currentTerm, 'subject' => $pastClassSubject, 'locked' => false],
            ['term' => $pastTerm, 'subject' => $currentClassSubject, 'locked' => false],
        ] as $record) {
            Result::create([
                'student_id' => $student->id,
                'class_subject_id' => $record['subject']->id,
                'term_id' => $record['term']->id,
                'ca_score' => 20,
                'exam_score' => 40,
                'is_locked' => $record['locked'],
            ]);
        }
        $secondStudent = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-ADMIN-ATTENDANCE',
        ]);
        foreach ([
            ['student' => $student, 'term' => $currentTerm, 'date' => '2026-10-02', 'status' => 'present'],
            ['student' => $secondStudent, 'term' => $currentTerm, 'date' => '2026-10-02', 'status' => 'absent'],
            ['student' => $student, 'term' => $pastTerm, 'date' => '2025-10-02', 'status' => 'late'],
        ] as $attendance) {
            Attendance::create([
                'student_id' => $attendance['student']->id,
                'class_id' => $class->id,
                'term_id' => $attendance['term']->id,
                'date' => $attendance['date'],
                'status' => $attendance['status'],
                'marked_by' => $admin->id,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertSame([
            'recorded' => 2,
            'locked' => 1,
            'unlocked' => 1,
        ], $response->viewData('resultMetrics'));
        $this->assertSame(['Oct 2, 2026'], $response->viewData('attendanceChart')['labels']);
        $this->assertSame([
            ['label' => 'Present', 'data' => [1]],
            ['label' => 'Absent', 'data' => [1]],
        ], $response->viewData('attendanceChart')['datasets']);
        $response->assertSee('Current term result records');
        $response->assertSee('All-term Finance Overview');
        $response->assertDontSee('Pending');
    }

    public function test_admin_result_metrics_are_unavailable_when_no_current_term_is_configured(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertNull($response->viewData('resultMetrics'));
        $response->assertSee('Unavailable because no current term is configured.');
    }
}
