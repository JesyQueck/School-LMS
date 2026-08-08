<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassAssignment;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
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
            ['student_id' => $studentId, 'term_id' => $term->id],
            [
                'class_teacher_remark' => $validated['comment'],
                'affective_domain' => $validated['affective_domain'] ?? null,
                'psychomotor_assessment' => $validated['psychomotor_assessment'] ?? null,
                'health_remarks' => $validated['health_remarks'] ?? null,
                'status' => $hasScores ? 'subject_scores_pending' : 'draft',
            ]
        );

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
        $reportCard->update(['status' => 'pending_principal_approval']);

        return redirect()->route('teacher.report-cards.index')->with('status', 'Report card submitted for principal review.');
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
}