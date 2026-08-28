<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassAssignmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_fillable_attributes_cannot_be_set_via_create(): void
    {
        $user = User::factory()->create();
        $class = SchoolClass::create(['name' => 'JSS 1']);

        $student = Student::create([
            'user_id' => $user->id,
            'admission_no' => 'ADM-001',
            'class_id' => $class->id,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'status' => 'active',
            'remember_token' => 'should-not-be-set',
        ]);

        $this->assertNull($student->getOriginal('remember_token'));
    }

    public function test_non_fillable_attributes_cannot_be_set_via_fill(): void
    {
        $user = User::factory()->create();
        $class = SchoolClass::create(['name' => 'JSS 1']);

        $student = Student::create([
            'user_id' => $user->id,
            'admission_no' => 'ADM-001',
            'class_id' => $class->id,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'status' => 'active',
        ]);

        $student->fill([
            'admission_no' => 'ADM-OVERRIDE',
            'remember_token' => 'hijacked',
        ]);

        $this->assertSame('ADM-001', $student->getOriginal('admission_no'));
        $this->assertNull($student->getOriginal('remember_token'));
    }

    public function test_result_id_is_not_mass_assignable(): void
    {
        $user = User::factory()->create();
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $subject = Subject::create(['name' => 'Math']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
        $session = AcademicSession::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-07-31',
            'is_current' => true,
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2024-09-01',
            'end_date' => '2024-12-20',
            'is_current' => true,
        ]);
        $student = Student::create([
            'user_id' => $user->id,
            'admission_no' => 'ADM-001',
            'class_id' => $class->id,
            'first_name' => 'Test',
            'last_name' => 'Student',
        ]);

        $result = Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'id' => 999999,
        ]);

        $this->assertNotSame(999999, $result->id);
    }
}
