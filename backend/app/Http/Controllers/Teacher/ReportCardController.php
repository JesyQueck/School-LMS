<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\Student;
use App\Models\Term;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportCardController extends Controller
{
    use AuditsActions;
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
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
                ->whereIn('term_id', [$term->id])
                ->get();
                
            $subjectAssignments = \App\Models\TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get();
        }

        return view('teacher.report-cards.index', compact('students', 'term', 'reportCards', 'subjectAssignments', 'classAssignment'));
    }

    public function store(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'affective_domain' => ['nullable', 'string', 'max:1000'],
            'psychomotor_assessment' => ['nullable', 'string', 'max:1000'],
            'health_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $term = Term::find($validated['term_id']);
        $studentId = $validated['student_id'];
        $student = Student::find($studentId);

        $hasScores = $student->results()->where('term_id', $term->id)->exists();
        
$reportCard = ReportCard::updateOrCreate(
            ['student_id' => $studentId, 'term_id' => $termId],
            [
                'class_teacher_remark' => $validated['comment'],
                'affective_domain' => $validated['affective_domain'] ?? null,
                'psychomotor_assessment' => $validated['psychomotor_assessment'] ?? null,
                'health_remarks' => $validated['health_remarks'] ?? null,
                'status' => $hasScores ? 'subject_scores_pending' : 'draft',
            ]
        );

        $this->audit($request, 'report_card.comment_added', ReportCard::class, $reportCard->id, null, [
            'student_id' => $studentId,
            'comment' => $validated['comment'],
            'affective_domain' => $validated['affective_domain'] ?? null,
            'psychomotor_assessment' => $validated['psychomotor_assessment'] ?? null,
            'health_remarks' => $validated['health_remarks'] ?? null,
        ]);

        return redirect()->route('teacher.report-cards.index')->with('status', 'Comment saved successfully.');
    }
    
    public function getStudentResults($studentId)
    {
        $teacher = auth()->user()->teacher;
        
        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();
        
        if (!$classAssignment) {
            return response()->json(['error' => 'No class assignment found'], 404);
        }
        
        $student = Student::where('id', $studentId)
            ->where('class_id', $classAssignment->class_id)
            ->with('class', 'user')
            ->first();
            
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $term = Term::find($classAssignment->term_id);
        
        $results = Result::where('student_id', $studentId)
            ->where('term_id', $term->id)
            ->with('classSubject.subject')
            ->get();

        $formattedResults = $results->map(function($result) {
            $total = ($result->ca_score ?: 0) + ($result->exam_score ?: 0);
            
            $gradingScale = [
                ['min' => 75, 'grade' => 'A1', 'remark' => 'Excellent'],
                ['min' => 70, 'grade' => 'B2', 'remark' => 'Very Good'],
                ['min' => 65, 'grade' => 'B3', 'remark' => 'Good'],
                ['min' => 60, 'grade' => 'C4', 'remark' => 'Credit'],
                ['min' => 55, 'grade' => 'C5', 'remark' => 'Credit'],
                ['min' => 50, 'grade' => 'C6', 'remark' => 'Credit'],
                ['min' => 45, 'grade' => 'D7', 'remark' => 'Pass'],
                ['min' => 40, 'grade' => 'E8', 'remark' => 'Pass'],
            ];
            
            $grade = 'F9';
            $remark = 'Fail';
            foreach ($gradingScale as $g) {
                if ($total >= $g['min']) {
                    $grade = $g['grade'];
                    $remark = $g['remark'];
                    break;
                }
            }
            
            return [
                'subject' => $result->classSubject->subject->name ?? 'Unknown',
                'ca' => $result->ca_score,
                'exam' => $result->exam_score,
                'total' => $total,
                'grade' => $grade,
                'remark' => $remark,
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

        $attendance = $presentDays . '/' . max(1, $attendanceRecords) . ' days present';

        $studentData = [
            'id' => $student->id,
            'name' => $student->user->name ?? '',
            'full_name' => $student->full_name ?? $student->user->name ?? '',
            'admission_no' => $student->admission_no ?? '',
            'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : null,
            'class' => $student->class,
        ];

        return response()->json([
            'student' => $studentData,
            'term' => $term,
            'results' => $formattedResults,
            'reportCard' => $reportCard,
            'attendance' => $attendance,
        ]);
    }

    public function submitForReview(ReportCard $reportCard, Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        $classAssignment = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (!$classAssignment) {
            return back()->with('error', 'No active class assignment found.');
        }

        $hasScores = Result::where('student_id', $reportCard->student_id)
            ->where('term_id', $classAssignment->term_id)
            ->whereNotNull('ca_score')
            ->whereNotNull('exam_score')
            ->exists();

        if (!$hasScores) {
            return back()->with('error', 'Cannot submit report card: subject scores are still pending for this student.');
        }

        if (empty($reportCard->class_teacher_remark) && 
            empty($reportCard->affective_domain) && 
            empty($reportCard->psychomotor_assessment) && 
            empty($reportCard->health_remarks)) {
            return back()->with('error', 'Cannot submit report card: class teacher comments are required.');
        }

        $reportCard->update(['status' => 'pending_principal_approval']);

        $this->audit($request, 'report_card.submitted', ReportCard::class, $reportCard->id, 
            ['status' => 'review'], 
            ['status' => 'pending_principal_approval']);

        $this->notifyPrincipalPendingApproval();

        return redirect()->route('teacher.report-cards.index')->with('status', 'Report card submitted for principal review.');
    }

    protected function notifyPrincipalPendingApproval(): void
    {
        $pendingCount = ReportCard::where('status', 'pending_principal_approval')->count();
        $adminUser = \App\Models\User::where('role', 'admin')->first();
        if ($adminUser) {
            \App\Models\Notification::create([
                'user_id' => $adminUser->id,
                'recipient_role' => 'admin',
                'title' => 'Report Cards Awaiting Approval',
                'message' => $pendingCount . ' report cards are awaiting your approval.',
                'type' => 'info',
                'is_read' => false,
            ]);
        }
    }

    public function download(ReportCard $reportCard)
    {
        $reportCard->load([
            'student.results.classSubject.subject',
            'student.user',
            'student.class',
            'term'
        ]);

        $pdf = Pdf::loadView('pdf.report-card', compact('reportCard'));

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }

    public function classPerformance()
    {
        $teacher = auth()->user()->teacher;
        
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
        $teacher = auth()->user()->teacher;
        
        $classAssignment = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();

        if (!$classAssignment) {
            return response()->json(['error' => 'No class assignment found'], 404);
        }

        $subjectAssignments = \App\Models\TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
            ->where('classSubject.class_id', $classAssignment->class_id)
            ->where('is_active', true)
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

        $allScoresSubmitted = collect($submissionProgress)->every(fn($p) => $p['completed']);
        
        $attendanceCount = 0;
        if ($totalStudents > 0) {
            $attendanceCount = \App\Models\Attendance::whereIn('student_id', $students->pluck('id'))
                ->where('term_id', $classAssignment->term_id)
                ->whereDate('date', '<=', now())
                ->distinct('student_id')
                ->count('student_id');
        }
        $attendanceSubmitted = $attendanceCount > 0;

        $reportCard = ReportCard::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $classAssignment->term_id)
            ->first();
        
        $commentsCompleted = false;
        if ($reportCard) {
            $hasAllComments = $students->every(function($student) use ($classAssignment) {
                $rc = ReportCard::where('student_id', $student->id)
                    ->where('term_id', $classAssignment->term_id)
                    ->first();
                return $rc && ($rc->class_teacher_remark || $rc->affective_domain || $rc->psychomotor_assessment || $rc->health_remarks);
            });
            $commentsCompleted = $hasAllComments;
        }

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