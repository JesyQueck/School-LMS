<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $reportCards = $student->reportCards()
            ->where('is_published', true)
            ->with(['term.academicSession', 'student.class'])
            ->latest()
            ->get()
            ->groupBy(fn ($rc) => $rc->term->academicSession->name ?? 'Unknown Session');

        return view('student.report-cards', compact('student', 'reportCards'));
    }

    public function download(Request $request, ReportCard $reportCard)
    {
        $student = $request->user()->student;

        if ($reportCard->student_id !== $student->id || ! $reportCard->is_published) {
            abort(403);
        }

        $reportCard->load(['student.results.classSubject.subject', 'student.user', 'student.class', 'student.attendance', 'term']);
        $term = $reportCard->term;

        $pdf = Pdf::loadView('pdf.report-card', compact('reportCard', 'term'));

        return $pdf->download("report-card-{$student->admission_no}-{$reportCard->term->name}.pdf");
    }
}
