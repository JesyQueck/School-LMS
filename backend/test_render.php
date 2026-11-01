<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear view cache
$cachePath = 'storage/framework/views';
foreach (glob($cachePath . '/*') as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        unlink($file);
    }
}

$suffix = uniqid();

// Create mock data with unique names
$studentUser = App\Models\User::factory()->create(['role' => 'student']);
$class = App\Models\SchoolClass::create(['name' => 'TestClass_' . $suffix]);
$student = App\Models\Student::create([
    'user_id' => $studentUser->id,
    'class_id' => $class->id,
    'admission_no' => 'TEST_' . $suffix,
    'first_name' => 'Test',
    'last_name' => 'Student',
]);

$session = App\Models\AcademicSession::create([
    'name' => 'TestSession_' . $suffix,
    'start_date' => '2026-09-01',
    'end_date' => '2027-07-31',
    'is_current' => true,
]);

$term = App\Models\Term::create([
    'academic_session_id' => $session->id,
    'name' => 'First Term',
    'start_date' => '2026-09-01',
    'end_date' => '2026-12-20',
    'is_current' => true,
]);

$subject = App\Models\Subject::create(['name' => 'Math_' . $suffix]);
$classSubject = App\Models\ClassSubject::create([
    'class_id' => $class->id,
    'subject_id' => $subject->id,
]);

App\Models\Result::create([
    'student_id' => $student->id,
    'class_subject_id' => $classSubject->id,
    'term_id' => $term->id,
    'ca_score' => 30,
    'exam_score' => 50,
    'total' => 80,
    'grade' => 'A1',
    'remark' => 'Excellent',
]);

$reportCard = App\Models\ReportCard::create([
    'student_id' => $student->id,
    'term_id' => $term->id,
    'class_id' => $class->id,
    'class_teacher_remark' => 'Good student',
    'principal_remark' => 'Well done',
    'status' => 'draft',
]);

$reportCard->load([
    'student.user',
    'student.schoolClass',
    'term',
    'student.results' => function ($query) use ($reportCard) {
        $query->where('term_id', $reportCard->term_id)->with('classSubject.subject');
    },
]);

echo "=== Rendering admin.report-cards.show ===\n";
try {
    $html = view('admin.report-cards.show', ['reportCard' => $reportCard])->render();
    echo "SUCCESS (length: " . strlen($html) . ")\n";
} catch (\Throwable $e) {
    echo "ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Cleanup
$reportCard->delete();
$student->delete();
$studentUser->delete();
$class->delete();
$session->delete();
$term->delete();
$subject->delete();
$classSubject->delete();
App\Models\Result::where('student_id', $student->id)->delete();

foreach (glob($cachePath . '/*') as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        unlink($file);
    }
}

echo "\nDone.\n";
