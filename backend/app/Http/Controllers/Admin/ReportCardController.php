<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Term;
use App\Services\ReportCardService;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected ReportCardService $reportCardService,
    ) {}

    public function index()
    {
        return view('admin.report-cards.index', [
            'reportCards' => ReportCard::with(['student', 'term'])->get(),
            'students' => Student::with('class')->get(),
            'terms' => Term::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_teacher_remark' => ['nullable', 'string'],
            'principal_remark' => ['nullable', 'string'],
            'position_in_class' => ['nullable', 'integer'],
            'total_students_in_class' => ['nullable', 'integer'],
            'next_term_begins' => ['nullable', 'date'],
        ]);

        $this->reportCardService->generateReportCard($data);

        $reportCard = ReportCard::query()->latest('id')->first();
        $this->audit($request, 'report_card.created', ReportCard::class, $reportCard?->id, null, $data);

        return redirect()->route('admin.report-cards')->with('status', 'Report card created.');
    }

    public function publish(ReportCard $reportCard, Request $request)
    {
        $this->reportCardService->publish($reportCard, $request->user());

        $this->audit($request, 'report_card.published', ReportCard::class, $reportCard->id, ['is_published' => false], ['is_published' => true]);

        return redirect()->route('admin.report-cards')->with('status', 'Report card published.');
    }

    public function download(ReportCard $reportCard)
    {
        $reportCard->load(['student.results.classSubject.subject', 'student.user', 'student.class', 'term']);

        $pdf = Pdf::loadView('pdf.report-card', compact('reportCard'));

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }
}
