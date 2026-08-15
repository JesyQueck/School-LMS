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

        abort_if(! $student, 403, 'Student profile not found.');

        $reportCards = $student->reportCards()
            ->where('status', ReportCard::STATUS_PUBLISHED)
            ->with(['term.academicSession', 'student.schoolClass'])
            ->latest()
            ->get()
            ->groupBy(fn ($rc) => $rc->term->academicSession->name ?? 'Unknown Session');

        return view('student.report-cards', compact('student', 'reportCards'));
    }

    public function preview(Request $request, ReportCard $reportCard)
    {
        $this->ensurePublishedForStudent($request, $reportCard);

        return view('student.report-cards.preview', [
            'reportCard' => $reportCard,
        ]);
    }

    public function render(Request $request, ReportCard $reportCard)
    {
        $this->ensurePublishedForStudent($request, $reportCard);

        return view('pdf.report-card', $this->prepareReportCardData($reportCard));
    }

    public function download(Request $request, ReportCard $reportCard)
    {
        $this->ensurePublishedForStudent($request, $reportCard);

        $student = $request->user()->student;

        abort_if(! $student, 403, 'Student profile not found.');

        $pdf = Pdf::loadView('pdf.report-card', $this->prepareReportCardData($reportCard))
            ->setOption('defaultMediaType', 'print')
            ->setOption('isPhp', true)
            ->setOption('isHtml5Print', true)
            ->setOption('defaultFont', 'Inter');

        return $pdf->download("report-card-{$student->admission_no}-{$reportCard->term->name}.pdf");
    }

    /**
     * Abort with 403 unless the authenticated student owns and may view
     * (i.e. the card is published) the given report card.
     */
    protected function ensurePublishedForStudent(Request $request, ReportCard $reportCard): void
    {
        $student = $request->user()->student;

        abort_if(! $student, 403, 'Student profile not found.');

        if ($reportCard->student_id !== $student->id || ! $reportCard->isPublished()) {
            abort(403);
        }
    }

    /**
     * Load the relations and assemble the shared view-data array used by
     * both render() (HTML preview) and download() (Dompdf). Keeping a single
     * source of truth guarantees the preview is always identical to the PDF.
     */
    protected function prepareReportCardData(ReportCard $reportCard): array
    {
        $reportCard->load([
            'student.results.classSubject.subject',
            'student.schoolClass',
            'student.user',
            'student.attendance',
            'term',
        ]);

        $term = $reportCard->term;
        $school = $reportCard->student->school ?? null;

        return compact('reportCard', 'school', 'term');
    }
}
