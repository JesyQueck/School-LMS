<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;

class ParentReportCardController extends Controller
{
    public function index($studentId)
    {
        $student = Student::with(['user', 'schoolClass'])->findOrFail($studentId);

        $this->authorize('view', $student);

        $reportCardTermIds = ReportCard::where('student_id', $studentId)
            ->where('status', ReportCard::STATUS_PUBLISHED)
            ->pluck('term_id')
            ->unique();

        $termsQuery = Term::query();

        if ($student->academic_session_id) {
            $termsQuery->where('academic_session_id', $student->academic_session_id);
        }

        // Also include terms that have published report cards for this student
        if ($reportCardTermIds->isNotEmpty()) {
            $termsQuery->orWhereIn('id', $reportCardTermIds);
        }

        $terms = $termsQuery
            ->orderBy('id')
            ->with(['reportCards' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId)->where('status', ReportCard::STATUS_PUBLISHED);
            }])
            ->get();

        return view('parent.report-cards', compact('student', 'terms'));
    }

    public function download($studentId, ReportCard $reportCard)
    {
        $student = Student::with(['user', 'schoolClass'])->findOrFail($studentId);

        $this->authorize('view', $student);

        if ($reportCard->student_id !== $student->id || ! $reportCard->isPublished()) {
            abort(403, 'Report card not available.');
        }

        $reportCard->load(['student.results.classSubject.subject', 'student.schoolClass', 'student.user', 'term']);
        $term = $reportCard->term;
        $school = $reportCard->student->school ?? null;
        $pdf = Pdf::loadView('pdf.report-card', compact('reportCard', 'school', 'term'))
            ->setOption('defaultMediaType', 'print')
            ->setOption('isPhp', true)
            ->setOption('isHtml5Print', true)
            ->setOption('defaultFont', 'Inter');

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }
}
