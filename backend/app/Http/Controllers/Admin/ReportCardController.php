<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
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

        $reportCard = ReportCard::updateOrCreate(
            ['student_id' => $studentId, 'term_id' => $termId],
            [
                'class_teacher_remark' => $studentData['class_teacher_remark'] ?? null,
                'principal_remark' => $studentData['principal_remark'] ?? null,
                'affective_domain' => $studentData['affective_domain'] ?? null,
                'psychomotor_assessment' => $studentData['psychomotor_assessment'] ?? null,
                'health_remarks' => $studentData['health_remarks'] ?? null,
                'position_in_class' => $studentData['position_in_class'] ?? null,
                'total_students_in_class' => $studentData['total_students_in_class'] ?? null,
            ]
        );

        $this->audit($request, 'report_card.updated', ReportCard::class, $reportCard->id, null, $studentData);

        return redirect()->route('admin.report-cards')->with('status', 'Report card saved.');
    }

    public function returnForCorrection(ReportCard $reportCard, Request $request)
    {
        $reportCard->update(['status' => 'returned']);

        return redirect()->route('admin.report-cards')->with('status', 'Report card returned for correction.');
    }

    public function approve(ReportCard $reportCard, Request $request)
    {
        $validated = $request->validate([
            'principal_remark' => ['nullable', 'string'],
            'promotion_decision' => ['nullable', 'string', 'in:promoted,repeated,transferred'],
            'position_in_class' => ['nullable', 'integer'],
            'total_students_in_class' => ['nullable', 'integer'],
            'next_term_begins' => ['nullable', 'date'],
        ]);

        $reportCard->update([
            'principal_remark' => $validated['principal_remark'] ?? null,
            'promotion_decision' => $validated['promotion_decision'] ?? null,
            'position_in_class' => $validated['position_in_class'] ?? $reportCard->position_in_class,
            'total_students_in_class' => $validated['total_students_in_class'] ?? $reportCard->total_students_in_class,
            'next_term_begins' => $validated['next_term_begins'] ?? $reportCard->next_term_begins,
            'status' => 'approved',
        ]);

        return redirect()->route('admin.report-cards')->with('status', 'Report card approved.');
    }

    public function publish(ReportCard $reportCard, Request $request)
    {
        $this->reportCardService->publish($reportCard, $request->user());

        $this->audit($request, 'report_card.published', ReportCard::class, $reportCard->id, ['is_published' => false], ['is_published' => true]);

        $reportCard->load('student.user');
        
        if ($reportCard->student && $reportCard->student->user) {
            \App\Models\Notification::create([
                'user_id' => $reportCard->student->user->id,
                'recipient_role' => 'student',
                'title' => 'Report Card Published',
                'message' => 'Your report card for ' . ($reportCard->term->name ?? 'Current Term') . ' has been published.',
                'type' => 'success',
                'is_read' => false,
            ]);
        }

        $parentUser = \App\Models\ParentProfile::where('student_id', $reportCard->student_id)?->first()?->user;
        if ($parentUser) {
            \App\Models\Notification::create([
                'user_id' => $parentUser->id,
                'recipient_role' => 'parent',
                'title' => 'Report Card Published',
                'message' => 'Report card for ' . ($reportCard->student->full_name ?? 'your child') . ' has been published.',
                'type' => 'info',
                'is_read' => false,
            ]);
        }

        return redirect()->route('admin.report-cards')->with('status', 'Report card published.');
    }

    public function download(ReportCard $reportCard)
    {
        $reportCard->load(['student.results.classSubject.subject', 'student.user', 'student.class', 'term']);

        $pdf = Pdf::loadView('pdf.report-card', compact('reportCard'));

        return $pdf->download("report-card-{$reportCard->student->admission_no}-{$reportCard->term->name}.pdf");
    }

    public function publishAll(Request $request)
    {
        $termId = $request->route('term_id') ?? $request->input('term_id');
        
        $updated = ReportCard::where('term_id', $termId)
            ->where('status', 'approved')
            ->update(['is_published' => true, 'status' => 'published']);

        $this->audit($request, 'report_cards.published_all', ReportCard::class, null, null, ['term_id' => $termId]);

        return redirect()->route('admin.report-cards')->with('status', "{$updated} report card(s) published successfully.");
    }
}
