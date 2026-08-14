<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassAssignment;
use App\Models\Student;
use App\Models\TeacherClassSubject;
use App\Services\ResultService;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class SubjectResultsController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected ResultService $resultService,
    ) {}

    public function scores()
    {
        $teacher = auth()->user()?->teacher;
        if (! $teacher) {
            abort(403, 'You do not have a teacher profile.');
        }

        $classAssignment = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (! $classAssignment) {
            return view('teacher.subject-results.scores', [
                'students' => collect(),
                'assignments' => collect(),
                'classAssignment' => null,
            ]);
        }

        $students = Student::where('class_id', $classAssignment->class_id)
            ->with(['results' => fn ($q) => $q->where('term_id', $classAssignment->term_id)])
            ->get();

        $assignments = TeacherClassSubject::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->with(['classSubject.subject', 'classSubject.class'])
            ->get();

        return view('teacher.subject-results.scores', compact('students', 'assignments', 'classAssignment'));
    }

    public function store(Request $request)
    {
        $teacher = auth()->user()?->teacher;
        if (! $teacher) {
            abort(403, 'You do not have a teacher profile.');
        }

        $classAssignment = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (! $classAssignment) {
            return redirect()->route('teacher.scores')->with('error', 'No active class assignment found.');
        }

        $validated = $request->validate([
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'results' => ['required', 'array'],
        ]);

        $subjectIdsProcessed = [];

        foreach ($request->input('results', []) as $classSubjectId => $studentScores) {
            if (! is_numeric($classSubjectId)) {
                continue;
            }

            $subjectIdsProcessed[] = $classSubjectId;

            foreach ($studentScores as $studentId => $scores) {
                $student = Student::find($studentId);
                if (! $student) {
                    continue;
                }

                $caScore = $scores['ca_score'] ?? null;
                $examScore = $scores['exam_score'] ?? null;

                if ($caScore !== null || $examScore !== null) {
                    $this->resultService->updateOrCreateResult([
                        'student_id' => $studentId,
                        'class_subject_id' => $classSubjectId,
                        'term_id' => $validated['term_id'],
                        'ca_score' => $caScore,
                        'exam_score' => $examScore,
                        'remark' => $scores['remark'] ?? null,
                    ], $teacher);
                }
            }
        }

        $this->audit($request, 'scores.entered', 'subject_results', null, null, [
            'teacher_id' => $teacher->id,
            'class_id' => $validated['class_id'],
            'term_id' => $validated['term_id'],
            'subjects' => $subjectIdsProcessed,
            'student_count' => count($request->input('results', [])),
        ]);

        return redirect()->route('teacher.scores')->with('status', 'Scores saved successfully.');
    }

    protected function logScoreChange(Request $request, string $action, array $details = []): void
    {
        $this->audit($request, $action, 'score_entry', null, null, $details);
    }
}
