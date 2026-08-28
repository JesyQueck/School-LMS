<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectResultsRequest;
use App\Models\ClassAssignment;
use App\Models\Student;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Services\ResultService;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class SubjectResultsController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected ResultService $resultService,
    ) {}

    public function scores(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            abort(403, 'You do not have a teacher profile.');
        }

        $classSubjectId = $request->query('class_subject_id');

        $assignments = TeacherClassSubject::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->with(['classSubject.subject', 'classSubject.class'])
            ->get();

        if (! $classSubjectId && $assignments->isNotEmpty()) {
            $classSubjectId = $assignments->first()->class_subject_id;
        }

        $selectedAssignment = null;
        $classAssignment = null;
        $students = collect();

        if ($classSubjectId) {
            $selectedAssignment = $assignments->firstWhere('class_subject_id', $classSubjectId);

            if ($selectedAssignment) {
                $classSubject = $selectedAssignment->classSubject;
                $term = Term::where('is_current', true)->first();

                if ($classSubject && $term) {
                    $classAssignment = new ClassAssignment;
                    $classAssignment->class_id = $classSubject->class_id;
                    $classAssignment->term_id = $term->id;
                    $classAssignment->class = $classSubject->class;

                    $students = Student::where('class_id', $classSubject->class_id)
                        ->with(['results' => fn ($q) => $q->where('term_id', $term->id)])
                        ->get();
                }
            }
        }

        $caMax = 30;
        $examMax = 70;

        if ($selectedAssignment && $selectedAssignment->classSubject) {
            $caMax = $selectedAssignment->classSubject->ca_max ?? 30;
            $examMax = $selectedAssignment->classSubject->exam_max ?? 70;
        }

        return view('teacher.subject-results.scores', compact(
            'students',
            'assignments',
            'classAssignment',
            'selectedAssignment',
            'classSubjectId',
            'caMax',
            'examMax'
        ));
    }

    public function store(StoreSubjectResultsRequest $request)
    {
        $teacher = $request->user()->teacher;
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

        $validated = $request->validated();

        if ($validated['term_id'] != $classAssignment->term_id) {
            return redirect()->route('teacher.scores')->with('error', 'Invalid term for your assignment.');
        }

        if ($validated['class_id'] != $classAssignment->class_id) {
            return redirect()->route('teacher.scores')->with('error', 'Invalid class for your assignment.');
        }

        $teacherSubjectIds = TeacherClassSubject::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('class_subject_id')
            ->toArray();

        $subjectIdsProcessed = [];

        foreach ($request->input('results', []) as $classSubjectId => $studentScores) {
            if (! is_numeric($classSubjectId)) {
                continue;
            }

            if (! in_array((int) $classSubjectId, $teacherSubjectIds, true)) {
                abort(403, 'You are not authorized to enter results for this subject.');
            }

            $subjectIdsProcessed[] = $classSubjectId;

            foreach ($studentScores as $studentId => $scores) {
                $student = Student::find($studentId);
                if (! $student) {
                    continue;
                }

                if ($student->class_id !== $classAssignment->class_id) {
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
                    ], $teacher->user);
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
}
