<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\Student;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Services\ReportCardService;
use App\Services\ResultService;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected ReportCardService $reportCardService,
        protected ResultService $resultService,
    ) {}

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $classAssignment = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        $term = $classAssignment?->term;
        $students = collect();
        $reportCards = collect();
        $subjectAssignments = collect();

        if ($classAssignment) {
            $students = Student::where('class_id', $classAssignment->class_id)->get();

            $reportCards = ReportCard::with(['student.user', 'term'])
                ->whereIn('student_id', $students->pluck('id'))
                ->where('term_id', $term->id)
                ->get();

            $subjectAssignments = TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get();
        }

        return view('teacher.report-cards.index', compact('students', 'term', 'reportCards', 'subjectAssignments', 'classAssignment'));
    }

    public function store(Request $request)
    {
        $teacher = $request->user()->teacher;

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'affective_domain' => ['nullable', 'string', 'max:1000'],
            'psychomotor_assessment' => ['nullable', 'string', 'max:1000'],
            'health_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $term = Term::findOrFail($validated['term_id']);
        $studentId = $validated['student_id'];

        $student = Student::findOrFail($studentId);

        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->where('term_id', $term->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (! $classAssignment || $classAssignment->class_id !== $student->class_id) {
            abort(403, 'You are not authorized to submit report card comments for this student.');
        }

        $hasScores = $student->results()->where('term_id', $term->id)->exists();

        $reportCard = $this->reportCardService->saveReportCard([
            'student_id' => $studentId,
            'term_id' => $term->id,
            'class_teacher_remark' => $validated['comment'],
            'affective_domain' => $validated['affective_domain'] ?? null,
            'psychomotor_assessment' => $validated['psychomotor_assessment'] ?? null,
            'health_remarks' => $validated['health_remarks'] ?? null,
        ]);

        $this->audit($request, 'report_card.comment_added', ReportCard::class, $reportCard->id, null, [
            'student_id' => $studentId,
            'comment' => $validated['comment'],
            'affective_domain' => $validated['affective_domain'] ?? null,
            'psychomotor_assessment' => $validated['psychomotor_assessment'] ?? null,
            'health_remarks' => $validated['health_remarks'] ?? null,
        ]);

        return redirect()->route('teacher.report-cards.index')->with('status', 'Comment saved successfully.');
    }

    public function submitForReview(Request $request, ReportCard $reportCard)
    {
        $teacher = $request->user()->teacher;

        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (! $classAssignment) {
            return back()->with('error', 'No active class assignment found.');
        }

        $hasScores = Result::where('student_id', $reportCard->student_id)
            ->where('term_id', $classAssignment->term_id)
            ->whereNotNull('ca_score')
            ->whereNotNull('exam_score')
            ->exists();

        if (! $hasScores) {
            return back()->with('error', 'Cannot submit report card: subject scores are still pending for this student.');
        }

        if (empty($reportCard->class_teacher_remark) &&
            empty($reportCard->affective_domain) &&
            empty($reportCard->psychomotor_assessment) &&
            empty($reportCard->health_remarks)) {
            return back()->with('error', 'Cannot submit report card: class teacher comments are required.');
        }

        $this->reportCardService->submitForApproval($reportCard, $request->user());

        $this->audit($request, 'report_card.submitted', ReportCard::class, $reportCard->id, null, [
            'status' => $reportCard->fresh()->status,
        ]);

        return redirect()->route('teacher.report-cards.index')->with('status', 'Report card submitted for principal review.');
    }

    public function getStudentResults(Request $request, int $studentId): JsonResponse
    {
        $teacher = $request->user()->teacher;

        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (! $classAssignment) {
            return response()->json(['error' => 'No class assignment found'], 404);
        }

        $student = Student::where('id', $studentId)
            ->where('class_id', $classAssignment->class_id)
            ->with('schoolClass', 'user')
            ->first();

        if (! $student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $term = $classAssignment->term;

        $results = Result::where('student_id', $studentId)
            ->where('term_id', $term->id)
            ->with('classSubject.subject')
            ->get();

        $formattedResults = $results->map(function (Result $result) {
            $total = $this->resultService->calculateTotal(
                $result->ca_score,
                $result->exam_score
            );
            $grading = $this->resultService->calculateGrade($total);

            return [
                'subject' => $result->classSubject->subject->name ?? 'Unknown',
                'ca' => $result->ca_score,
                'exam' => $result->exam_score,
                'total' => $total,
                'grade' => $result->grade ?? $grading['grade'],
                'remark' => $result->remark ?? $grading['remark'],
            ];
        });

        $reportCard = ReportCard::where('student_id', $studentId)
            ->where('term_id', $term->id)
            ->first();

        $attendanceRecords = $student->attendance()
            ->where('term_id', $term->id)
            ->count();
        $presentDays = $student->attendance()
            ->where('term_id', $term->id)
            ->where('status', 'present')
            ->count();

        $attendance = $presentDays.'/'.max(1, $attendanceRecords).' days present';

        $studentData = [
            'id' => $student->id,
            'name' => $student->user->name ?? '',
            'full_name' => $student->full_name ?? ($student->user->name ?? ''),
            'admission_no' => $student->admission_no ?? '',
            'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : null,
            'class' => $student->schoolClass,
        ];

        return response()->json([
            'student' => $studentData,
            'term' => $term,
            'results' => $formattedResults,
            'reportCard' => $reportCard,
            'attendance' => $attendance,
        ]);
    }

    public function download(Request $request, ReportCard $reportCard)
    {
        $teacher = $request->user()->teacher;

        $reportCard->load([
            'student.results.classSubject.subject',
            'student.user',
            'student.schoolClass',
            'student.attendance',
            'term',
        ]);

        $term = $reportCard->term;

        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->where('term_id', $term->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (! $classAssignment || $classAssignment->class_id !== $reportCard->student->class_id) {
            abort(403, 'You are not authorized to download this report card.');
        }

        if (! $reportCard->isPublished()) {
            abort(403, 'Only published report cards can be downloaded.');
        }

        $pdf = Pdf::loadView('pdf.report-card', [
            'reportCard' => $reportCard,
            'term' => $term,
            'school' => $reportCard->student->school ?? null,
        ])
            ->setOption('defaultMediaType', 'print')
            ->setOption('isPhp', true)
            ->setOption('isHtml5Print', true)
            ->setOption('defaultFont', 'Inter');

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }

    public function classPerformance(Request $request)
    {
        $teacher = $request->user()->teacher;

        $classAssignment = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        $students = collect();
        if ($classAssignment) {
            $students = Student::where('class_id', $classAssignment->class_id)
                ->with('results')
                ->get();
        }

        return view('teacher.class-performance', compact('students', 'classAssignment'));
    }

    public function getSubmissionProgress(Request $request)
    {
        $teacher = $request->user()->teacher;

        $classAssignment = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (! $classAssignment) {
            return response()->json(['error' => 'No class assignment found'], 404);
        }

        $subjectAssignments = TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
            ->where('is_active', true)
            ->whereHas('classSubject', fn ($q) => $q->where('class_id', $classAssignment->class_id))
            ->get();

        $students = Student::where('class_id', $classAssignment->class_id)->get();

        $submissionProgress = [];
        $totalStudents = $students->count();
        $totalSubjects = $subjectAssignments->count();

        foreach ($subjectAssignments as $assignment) {
            $subjectName = $assignment->classSubject->subject->name ?? 'Unknown';
            $teacherName = $assignment->teacher->user->name ?? $assignment->teacher->name ?? 'Unknown';

            $submittedCount = Result::where('class_subject_id', $assignment->class_subject_id)
                ->where('term_id', $classAssignment->term_id)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereNotNull('ca_score')
                ->whereNotNull('exam_score')
                ->distinct('student_id')
                ->count('student_id');

            $submissionProgress[] = [
                'subject' => $subjectName,
                'teacher' => $teacherName,
                'submitted' => $submittedCount,
                'total' => $totalStudents,
                'completed' => $submittedCount == $totalStudents,
            ];
        }

        $allScoresSubmitted = collect($submissionProgress)->every(fn ($p) => $p['completed']);

        $attendanceCount = 0;
        if ($totalStudents > 0) {
            $attendanceCount = Attendance::whereIn('student_id', $students->pluck('id'))
                ->where('term_id', $classAssignment->term_id)
                ->whereDate('date', '<=', now())
                ->distinct('student_id')
                ->count('student_id');
        }
        $attendanceSubmitted = $attendanceCount > 0;

        $hasReportCards = ReportCard::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $classAssignment->term_id)
            ->exists();

        $commentsCompleted = $hasReportCards
            ? Student::whereIn('id', $students->pluck('id'))
                ->get()
                ->every(function (Student $student) use ($classAssignment) {
                    $rc = ReportCard::where('student_id', $student->id)
                        ->where('term_id', $classAssignment->term_id)
                        ->first();

                    return $rc && ($rc->class_teacher_remark || $rc->affective_domain || $rc->psychomotor_assessment || $rc->health_remarks);
                })
            : false;

        $isReadyToSubmit = $allScoresSubmitted && $attendanceSubmitted && $commentsCompleted;

        return response()->json([
            'class' => $classAssignment->class->name ?? 'Unknown',
            'term' => $classAssignment->term->name ?? 'Current',
            'total_students' => $totalStudents,
            'total_subjects' => $totalSubjects,
            'submission_progress' => $submissionProgress,
            'all_scores_submitted' => $allScoresSubmitted,
            'attendance_submitted' => $attendanceSubmitted,
            'comments_completed' => $commentsCompleted,
            'is_ready_to_submit' => $isReadyToSubmit,
        ]);
    }
}
