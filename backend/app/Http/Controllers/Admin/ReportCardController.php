<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
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
        $currentTerm = Term::where('is_current', true)->first();

        return view('admin.report-cards.index', [
            'classes' => SchoolClass::with(['students.user'])->get(),
            'students' => Student::with('class.user', 'user')->get(),
            'subjects' => Subject::all(),
            'terms' => Term::all(),
            'currentTerm' => $currentTerm,
        ]);
    }

    public function classPerformance()
    {
        return view('admin.report-cards.performance');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'results' => ['nullable', 'array'],
            'results.*.student_id' => ['required', 'exists:students,id'],
            'results.*.class_teacher_remark' => ['nullable', 'string'],
            'results.*.principal_remark' => ['nullable', 'string'],
            'results.*.affective_domain' => ['nullable', 'string'],
            'results.*.psychomotor_assessment' => ['nullable', 'string'],
            'results.*.health_remarks' => ['nullable', 'string'],
            'results.*.position_in_class' => ['nullable', 'integer'],
            'results.*.total_students_in_class' => ['nullable', 'integer'],
        ]);

        $studentId = $validated['student_id'];
        $termId = $validated['term_id'];
        $studentData = $validated['results'][$studentId] ?? [];

        $existing = ReportCard::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->first();

        $reportCard = $this->reportCardService->saveReportCard(array_merge([
            'student_id' => $studentId,
            'term_id' => $termId,
        ], $studentData), $existing);

        $this->audit(
            $request,
            'report_card.updated',
            ReportCard::class,
            $reportCard->id,
            null,
            $studentData
        );

        return redirect()->route('admin.report-cards.index')->with('status', 'Report card saved.');
    }

    public function returnForCorrection(Request $request, ReportCard $reportCard)
    {
        $oldStatus = $reportCard->status;
        $reportCard = $this->reportCardService->returnForCorrection($reportCard);

        $this->audit(
            $request,
            'report_card.returned',
            ReportCard::class,
            $reportCard->id,
            ['status' => $oldStatus],
            ['status' => $reportCard->status]
        );

        return redirect()->route('admin.report-cards.index')->with('status', 'Report card returned for correction.');
    }

    public function approve(Request $request, ReportCard $reportCard)
    {
        $validated = $request->validate([
            'principal_remark' => ['nullable', 'string'],
            'promotion_decision' => ['nullable', 'string', 'in:promoted,repeated,transferred'],
            'position_in_class' => ['nullable', 'integer'],
            'total_students_in_class' => ['nullable', 'integer'],
            'next_term_begins' => ['nullable', 'date'],
        ]);

        $reportCard->update([
            'principal_remark' => $validated['principal_remark'] ?? $reportCard->principal_remark,
            'promotion_decision' => $validated['promotion_decision'] ?? $reportCard->promotion_decision,
            'position_in_class' => $validated['position_in_class'] ?? $reportCard->position_in_class,
            'total_students_in_class' => $validated['total_students_in_class'] ?? $reportCard->total_students_in_class,
            'next_term_begins' => $validated['next_term_begins'] ?? $reportCard->next_term_begins,
        ]);

        $oldStatus = $reportCard->status;
        $this->reportCardService->approve($reportCard);

        $this->audit(
            $request,
            'report_card.approved',
            ReportCard::class,
            $reportCard->id,
            ['status' => $oldStatus],
            ['status' => $reportCard->fresh()->status]
        );

        return redirect()->route('admin.report-cards.index')->with('status', 'Report card approved.');
    }

    public function publish(Request $request, ReportCard $reportCard)
    {
        $oldStatus = $reportCard->status;
        $oldPublished = $reportCard->is_published;

        $this->reportCardService->publish($reportCard, $request->user());

        $this->audit(
            $request,
            'report_card.published',
            ReportCard::class,
            $reportCard->id,
            ['status' => $oldStatus, 'is_published' => $oldPublished],
            ['status' => $reportCard->fresh()->status, 'is_published' => true]
        );

        $reportCard->load('student.user');

        return redirect()->route('admin.report-cards.index')->with('status', 'Report card published.');
    }

    public function download(Request $request, ReportCard $reportCard)
    {
        if (! $reportCard->is_published) {
            abort(403, 'Only published report cards can be downloaded.');
        }

        $reportCard->load([
            'student.results.classSubject.subject',
            'student.user',
            'student.class',
            'student.attendance',
            'term',
        ]);

        $pdf = Pdf::loadView('pdf.report-card', [
            'reportCard' => $reportCard,
            'term' => $reportCard->term,
        ]);

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }

    public function publishAll(Request $request, Term $term)
    {
        $result = $this->reportCardService->publishAll($term->id, $request->user());

        $this->audit(
            $request,
            'report_cards.published_all',
            ReportCard::class,
            null,
            null,
            ['term_id' => $term->id, 'published' => $result['published'], 'skipped' => $result['skipped']]
        );

        return redirect()->route('admin.report-cards.index')
            ->with('status', "{$result['published']} report card(s) published. {$result['skipped']} skipped.");
    }
}
