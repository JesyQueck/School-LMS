<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\User;
use App\Services\ResultService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createTestData(): array
    {
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

        $otherClass = SchoolClass::create(['name' => 'JSS 2']);
        $otherSubject = Subject::create(['name' => 'English']);
        $otherClassSubject = ClassSubject::create([
            'class_id' => $otherClass->id,
            'subject_id' => $otherSubject->id,
            'is_compulsory' => true,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM401',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'TCH001',
        ]);

        ClassAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'term_id' => $term->id,
            'academic_session_id' => $session->id,
        ]);

        TeacherClassSubject::create([
            'teacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
            'is_active' => true,
        ]);

        $adminUser = User::factory()->create(['role' => 'admin']);

        return compact(
            'session',
            'term',
            'class',
            'subject',
            'classSubject',
            'otherClass',
            'otherSubject',
            'otherClassSubject',
            'student',
            'studentUser',
            'teacher',
            'teacherUser',
            'adminUser'
        );
    }

    public function test_result_service_calculates_total_correctly(): void
    {
        $resultService = app(ResultService::class);

        $this->assertEquals(75.0, $resultService->calculateTotal(30, 45));
        $this->assertEquals(30.0, $resultService->calculateTotal(30, null));
        $this->assertEquals(45.0, $resultService->calculateTotal(null, 45));
        $this->assertNull($resultService->calculateTotal(null, null));
    }

    public function test_result_service_calculate_grade_returns_correct_grades(): void
    {
        $resultService = app(ResultService::class);

        $this->assertEquals(['grade' => 'A1', 'remark' => 'Excellent'], $resultService->calculateGrade(75));
        $this->assertEquals(['grade' => 'A1', 'remark' => 'Excellent'], $resultService->calculateGrade(100));
        $this->assertEquals(['grade' => 'B2', 'remark' => 'Very Good'], $resultService->calculateGrade(70));
        $this->assertEquals(['grade' => 'B3', 'remark' => 'Good'], $resultService->calculateGrade(65));
        $this->assertEquals(['grade' => 'C4', 'remark' => 'Credit'], $resultService->calculateGrade(60));
        $this->assertEquals(['grade' => 'C5', 'remark' => 'Credit'], $resultService->calculateGrade(55));
        $this->assertEquals(['grade' => 'C6', 'remark' => 'Credit'], $resultService->calculateGrade(50));
        $this->assertEquals(['grade' => 'D7', 'remark' => 'Pass'], $resultService->calculateGrade(45));
        $this->assertEquals(['grade' => 'E8', 'remark' => 'Pass'], $resultService->calculateGrade(40));
        $this->assertEquals(['grade' => 'F9', 'remark' => 'Fail'], $resultService->calculateGrade(39));
        $this->assertEquals(['grade' => null, 'remark' => null], $resultService->calculateGrade(null));
    }

    public function test_result_service_rejects_negative_ca_score(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CA score cannot be negative');

        app(ResultService::class)->updateOrCreateResult([
            'student_id' => 1,
            'class_subject_id' => 1,
            'term_id' => 1,
            'ca_score' => -5,
            'exam_score' => 50,
        ], User::factory()->create());
    }

    public function test_result_service_rejects_score_above_100(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Exam score cannot exceed 100');

        app(ResultService::class)->updateOrCreateResult([
            'student_id' => 1,
            'class_subject_id' => 1,
            'term_id' => 1,
            'ca_score' => 30,
            'exam_score' => 150,
        ], User::factory()->create());
    }

    public function test_result_service_creates_result_with_correct_total_and_grade(): void
    {
        $data = $this->createTestData();
        $this->actingAs($data['adminUser']);

        $resultService = app(ResultService::class);

        $result = $resultService->createResult([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 45,
        ], $data['adminUser']);

        $this->assertEquals(75.0, (float) $result->total);
        $this->assertEquals('A1', $result->grade);
        $this->assertFalse($result->is_locked);
    }

    public function test_result_service_updates_existing_result_with_recalculated_total(): void
    {
        $data = $this->createTestData();
        $this->actingAs($data['adminUser']);

        $resultService = app(ResultService::class);

        $resultService->createResult([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 20,
            'exam_score' => 50,
        ], $data['adminUser']);

        $updated = $resultService->updateOrCreateResult([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 50,
        ], $data['adminUser']);

        $this->assertEquals(80.0, (float) $updated->total);
        $this->assertEquals('A1', $updated->grade);
    }

    public function test_result_service_throws_on_locked_result_modification(): void
    {
        $data = $this->createTestData();
        $this->actingAs($data['adminUser']);

        $result = Result::create([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'grade' => 'A1',
            'is_locked' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('locked');

        app(ResultService::class)->updateOrCreateResult([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 40,
            'exam_score' => 50,
        ], $data['adminUser']);
    }

    public function test_locked_result_blocks_direct_eloquent_update(): void
    {
        $data = $this->createTestData();

        $result = Result::create([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'is_locked' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $result->update(['ca_score' => 99]);
    }

    public function test_locked_result_allows_unlocking_via_eloquent(): void
    {
        $data = $this->createTestData();

        $result = Result::create([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'is_locked' => true,
        ]);

        $result->update(['is_locked' => false]);
        $result->refresh();

        $this->assertFalse($result->is_locked);
    }

    public function test_model_recalculates_total_on_save(): void
    {
        $data = $this->createTestData();

        $result = new Result([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 25,
            'exam_score' => 45,
        ]);
        $result->save();

        $result->refresh();
        $this->assertEquals(70.0, (float) $result->total);
    }

    public function test_teacher_cannot_enter_results_for_unauthorized_class_subject(): void
    {
        $data = $this->createTestData();
        $teacherUser = $data['teacherUser'];
        $this->actingAs($teacherUser);

        $teacher = $teacherUser->teacher;

        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        $student = $data['student'];

        $response = $this->post('/teacher/scores', [
            'term_id' => $classAssignment->term_id,
            'class_id' => $classAssignment->class_id,
            'results' => [
                $data['otherClassSubject']->id => [
                    $student->id => [
                        'ca_score' => 30,
                        'exam_score' => 50,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_teacher_cannot_submit_results_for_wrong_term(): void
    {
        $data = $this->createTestData();
        $teacherUser = $data['teacherUser'];
        $this->actingAs($teacherUser);

        $teacher = $teacherUser->teacher;

        $otherTerm = Term::create([
            'academic_session_id' => $data['session']->id,
            'name' => 'Second Term',
            'start_date' => '2027-01-01',
            'end_date' => '2027-03-31',
            'is_current' => false,
        ]);

        $response = $this->post('/teacher/scores', [
            'term_id' => $otherTerm->id,
            'class_id' => $data['class']->id,
            'results' => [
                $data['classSubject']->id => [
                    $data['student']->id => [
                        'ca_score' => 30,
                        'exam_score' => 50,
                    ],
                ],
            ],
        ]);

        $response->assertRedirect('/teacher/scores');
        $response->assertSessionHas('error', 'Invalid term for your assignment.');
    }

    public function test_teacher_can_submit_results_for_authorized_subject(): void
    {
        $data = $this->createTestData();
        $teacherUser = $data['teacherUser'];
        $this->actingAs($teacherUser);

        $teacher = $teacherUser->teacher;

        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        $response = $this->post('/teacher/scores', [
            'term_id' => $classAssignment->term_id,
            'class_id' => $classAssignment->class_id,
            'results' => [
                $data['classSubject']->id => [
                    $data['student']->id => [
                        'ca_score' => 30,
                        'exam_score' => 50,
                    ],
                ],
            ],
        ]);

        $response->assertRedirect('/teacher/scores');

        $this->assertDatabaseHas('results', [
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'total' => 80,
        ]);
    }

    public function test_teacher_can_not_enter_results_for_student_in_other_class(): void
    {
        $data = $this->createTestData();
        $teacherUser = $data['teacherUser'];
        $this->actingAs($teacherUser);

        $teacher = $teacherUser->teacher;

        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        $otherStudent = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $data['otherClass']->id,
            'admission_no' => 'ADM405',
        ]);

        $response = $this->post('/teacher/scores', [
            'term_id' => $classAssignment->term_id,
            'class_id' => $classAssignment->class_id,
            'results' => [
                $data['classSubject']->id => [
                    $otherStudent->id => [
                        'ca_score' => 30,
                        'exam_score' => 50,
                    ],
                ],
            ],
        ]);

        $response->assertRedirect('/teacher/scores');
        $this->assertDatabaseMissing('results', [
            'student_id' => $otherStudent->id,
        ]);
    }

    public function test_admin_can_create_result_via_service(): void
    {
        $data = $this->createTestData();
        $this->actingAs($data['adminUser']);

        $resultService = app(ResultService::class);

        $result = $resultService->createResult([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 40,
            'exam_score' => 50,
        ], $data['adminUser']);

        $this->assertEquals(90.0, (float) $result->total);
        $this->assertEquals('A1', $result->grade);
        $this->assertEquals($data['adminUser']->id, $result->submitted_by);
    }

    public function test_result_uses_one_authoritative_grading_scale(): void
    {
        $resultService = app(ResultService::class);

        $grade = $resultService->calculateGrade(72);
        $this->assertEquals('B2', $grade['grade']);
    }

    public function test_duplicate_result_not_allowed_by_unique_constraint(): void
    {
        $data = $this->createTestData();

        Result::create([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 20,
            'exam_score' => 50,
        ]);

        $this->expectException(QueryException::class);

        Result::create([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 30,
            'exam_score' => 50,
        ]);
    }

    public function test_direct_result_creation_auto_calculates_all_fields(): void
    {
        $data = $this->createTestData();

        $result = Result::create([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 35,
            'exam_score' => 45,
        ]);

        $this->assertSame(80.0, (float) $result->total);
        $this->assertSame('A1', $result->grade);
        $this->assertSame('Excellent', $result->remark);
    }

    public function test_result_boundary_grades_via_direct_creation(): void
    {
        $data = $this->createTestData();

        $boundaries = [
            75 => ['A1', 'Excellent'],
            70 => ['B2', 'Very Good'],
            65 => ['B3', 'Good'],
            60 => ['C4', 'Credit'],
            55 => ['C5', 'Credit'],
            50 => ['C6', 'Credit'],
            45 => ['D7', 'Pass'],
            40 => ['E8', 'Pass'],
            39 => ['F9', 'Fail'],
        ];

        foreach ($boundaries as $total => $expected) {
            $subject = Subject::create(['name' => "Subj_{$total}"]);
            $classSubject = ClassSubject::create([
                'class_id' => $data['class']->id,
                'subject_id' => $subject->id,
            ]);

            $result = Result::create([
                'student_id' => $data['student']->id,
                'class_subject_id' => $classSubject->id,
                'term_id' => $data['term']->id,
                'ca_score' => $total,
                'exam_score' => 0,
            ]);

            $this->assertSame($expected[0], $result->grade, "Failed asserting grade for total {$total}");
            $this->assertSame($expected[1], $result->remark, "Failed asserting remark for total {$total}");
        }
    }

    public function test_result_service_and_model_produce_same_grading(): void
    {
        $data = $this->createTestData();
        $resultService = app(ResultService::class);

        $serviceResult = $resultService->createResult([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['classSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 35,
            'exam_score' => 45,
        ], $data['adminUser']);

        $modelResult = Result::create([
            'student_id' => $data['student']->id,
            'class_subject_id' => $data['otherClassSubject']->id,
            'term_id' => $data['term']->id,
            'ca_score' => 35,
            'exam_score' => 45,
        ]);

        $this->assertSame($serviceResult->total, $modelResult->total);
        $this->assertSame($serviceResult->grade, $modelResult->grade);
        $this->assertSame($serviceResult->remark, $modelResult->remark);
    }
}
