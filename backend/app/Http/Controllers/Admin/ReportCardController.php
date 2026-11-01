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

    public function index(Request $request)
    {
        $currentTerm = Term::where('is_current', true)->first();

        $classes = SchoolClass::with([
            'students.user',
            'formTeacher.user',
            'students.results' => function ($query) use ($currentTerm) {
                if ($currentTerm) {
                    $query->where('term_id', $currentTerm->id)
                        ->with('classSubject.subject');
                }
            },
            'students.reportCards' => function ($query) use ($currentTerm) {
                if ($currentTerm) {
                    $query->where('term_id', $currentTerm->id);
                }
            },
        ])->orderBy('name')->get();

        $subjects = Subject::all();
        $terms = Term::orderBy('start_date', 'desc')->get();

        return view('admin.report-cards.index', [
            'classes' => $classes,
            'subjects' => $subjects,
            'terms' => $terms,
            'currentTerm' => $currentTerm,
            'request' => $request,
        ]);
    }

    public function show(ReportCard $reportCard)
    {
        $reportCard->load([
            'student.user',
            'student.schoolClass',
            'term',
            'student.results' => function ($query) use ($reportCard) {
                $query->where('term_id', $reportCard->term_id)->with('classSubject.subject');
            },
        ]);

        return view('admin.report-cards.show', [
            'reportCard' => $reportCard,
        ]);
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
            'results.*.position_in_class' => ['nullable', 'integer', 'min:1'],
            'results.*.total_students_in_class' => ['nullable', 'integer', 'min:1'],
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

    public function saveClassReportCards(Request $request, SchoolClass $class)
    {
        $termId = $request->input('term_id') ?: Term::where('is_current', true)->first()?->id;

        if (! $termId) {
            return back()->with('status', 'Please select a term.');
        }

        $validated = $request->validate([
            'term_id' => ['nullable', 'exists:terms,id'],
            'students' => ['required', 'array'],
            'students.*.student_id' => ['required', 'exists:students,id'],
            'students.*.principal_remark' => ['nullable', 'string'],
            'students.*.promotion_decision' => ['nullable', 'string', 'in:promoted,repeated,transferred'],
            'students.*.position_in_class' => ['nullable', 'integer', 'min:1'],
            'students.*.total_students_in_class' => ['nullable', 'integer', 'min:1'],
            'students.*.next_term_begins' => ['nullable', 'date'],
            'students.*.review_date' => ['nullable', 'date'],
            'students.*.principal_signature' => ['nullable', 'string', 'max:255'],
        ]);

        $saved = 0;
        $skipped = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $termId, &$saved, &$skipped) {
            foreach ($validated['students'] as $studentData) {
                $existing = ReportCard::where('student_id', $studentData['student_id'])
                    ->where('term_id', $termId)
                    ->first();

                if ($existing && ! $existing->canEdit()) {
                    $skipped++;
                    continue;
                }

                $payload = array_merge([
                    'student_id' => $studentData['student_id'],
                    'term_id' => $termId,
                    'class_id' => $class->id,
                ], $studentData);
                unset($payload['student_id'], $payload['term_id'], $payload['class_id']);

                if ($existing) {
                    if (! $existing->canEdit()) {
                        $skipped++;
                        continue;
                    }
                    $existing->update($payload);
                } else {
                    ReportCard::create(array_merge([
                        'student_id' => $studentData['student_id'],
                        'term_id' => $termId,
                        'class_id' => $class->id,
                        'status' => ReportCard::STATUS_DRAFT,
                        'generated_at' => now(),
                    ], $payload));
                }

                $saved++;
            }
        });

         if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $saved . ' report card(s) saved for ' . $class->name . '. ' . ($skipped > 0 ? $skipped . ' skipped.' : ''),
            ]);
        }

        return back()->with('status', $saved . ' report card(s) saved for ' . $class->name . '. ' . ($skipped > 0 ? $skipped . ' skipped.' : ''));
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
            'position_in_class' => ['nullable', 'integer', 'min:1'],
            'total_students_in_class' => ['nullable', 'integer', 'min:1'],
            'next_term_begins' => ['nullable', 'date'],
            'review_date' => ['nullable', 'date'],
            'principal_signature' => ['nullable', 'string', 'max:255'],
        ]);

        $reportCard->update([
            'principal_remark' => $validated['principal_remark'] ?? $reportCard->principal_remark,
            'promotion_decision' => $validated['promotion_decision'] ?? $reportCard->promotion_decision,
            'position_in_class' => $validated['position_in_class'] ?? $reportCard->position_in_class,
            'total_students_in_class' => $validated['total_students_in_class'] ?? $reportCard->total_students_in_class,
            'next_term_begins' => $validated['next_term_begins'] ?? $reportCard->next_term_begins,
            'review_date' => $validated['review_date'] ?? $reportCard->review_date,
            'principal_signature' => $validated['principal_signature'] ?? $reportCard->principal_signature,
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
        $oldPublished = $reportCard->isPublished();

        $this->reportCardService->publish($reportCard, $request->user());

        $this->audit(
            $request,
            'report_card.published',
            ReportCard::class,
            $reportCard->id,
            ['status' => $oldStatus, 'was_published' => $oldPublished],
            ['status' => $reportCard->fresh()->status, 'published' => true]
        );

        $reportCard->load('student.user');

        return redirect()->route('admin.report-cards.index')->with('status', 'Report card published.');
    }

    public function unpublish(Request $request, ReportCard $reportCard)
    {
        $oldStatus = $reportCard->status;
        $oldPublished = $reportCard->isPublished();

        $this->reportCardService->unpublish($reportCard);

        $this->audit(
            $request,
            'report_card.unpublished',
            ReportCard::class,
            $reportCard->id,
            ['status' => $oldStatus, 'was_published' => $oldPublished],
            ['status' => $reportCard->fresh()->status, 'published' => false]
        );

        return redirect()->route('admin.report-cards.index')->with('status', 'Report card unpublished for corrections.');
    }

    public function download(Request $request, ReportCard $reportCard)
    {
        if (! $reportCard->isPublished()) {
            abort(403, 'Only published report cards can be downloaded.');
        }

        $reportCard->load([
            'student.results.classSubject.subject',
            'student.user',
            'student.schoolClass',
            'student.attendance',
            'term',
        ]);

        $pdf = Pdf::loadView('pdf.report-card', [
            'reportCard' => $reportCard,
            'term' => $reportCard->term,
            'school' => $reportCard->student->school ?? null,
        ])->setOption('defaultMediaType', 'print')
            ->setOption('isPhp', true)
            ->setOption('isHtml5Print', true)
            ->setOption('defaultFont', 'Inter');

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }

    public function preview(Request $request, ReportCard $reportCard)
    {
        $reportCard->load([
            'student.results.classSubject.subject',
            'student.user',
            'student.schoolClass',
            'student.attendance',
            'term',
        ]);

        return view('admin.report-cards.preview', [
            'reportCard' => $reportCard,
        ]);
    }

    public function render(Request $request, ReportCard $reportCard)
    {
        $reportCard->load([
            'student.results.classSubject.subject',
            'student.user',
            'student.schoolClass',
            'student.attendance',
            'term',
        ]);

        return view('pdf.report-card', [
            'reportCard' => $reportCard,
            'term' => $reportCard->term,
            'school' => $reportCard->student->school ?? null,
        ]);
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
