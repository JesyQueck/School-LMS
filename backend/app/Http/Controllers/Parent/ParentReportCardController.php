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
        $student = Student::with(['user', 'class'])->findOrFail($studentId);

        $terms = Term::orderBy('id')->with(['reportCards' => function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])->get();

        return view('parent.report-cards', compact('student', 'terms'));
    }

    public function download($studentId, ReportCard $reportCard)
    {
        $student = Student::with(['user', 'class'])->findOrFail($studentId);
        
        if ($reportCard->student_id !== $student->id || !$reportCard->is_published) {
            abort(403, 'Report card not available.');
        }

        $reportCard->load(['student.results.classSubject.subject', 'student.user', 'student.class', 'term']);
        $pdf = Pdf::loadView('pdf.report-card', compact('reportCard'));

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }
}
