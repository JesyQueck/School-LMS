<?php

namespace App\Http\Controllers\Bursar;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use App\Services\CsvExportService;
use App\Services\FeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected FeeService $feeService,
        protected CsvExportService $csvExportService,
    ) {}

    public function index(Request $request)
    {
        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();

        $dailyCollections = $this->getDailyCollections($currentTerm);
        $paymentMethodSummary = $this->getPaymentMethodSummary($currentTerm);
        $classCollectionSummary = $this->feeService->classSummary();
        $outstandingDebtors = $this->getOutstandingDebtors($request);

        return view('bursar.fee-collections', [
            'currentTerm' => $currentTerm,
            'dailyCollections' => $dailyCollections,
            'paymentMethodSummary' => $paymentMethodSummary,
            'classCollectionSummary' => $classCollectionSummary,
            'outstandingDebtors' => $outstandingDebtors,
            'terms' => Term::orderBy('id', 'desc')->get(),
            'classes' => SchoolClass::all(),
            'filters' => $request->only(['class_id', 'term_id', 'status', 'search']),
        ]);
    }

    public function dailyCollectionReport(Request $request)
    {
        $term = Term::findOrFail($request->input('term_id'));
        $classId = $request->input('class_id');

        $studentFees = StudentFee::with(['student.schoolClass', 'feeType', 'term', 'payments'])
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->where('term_id', $term->id)
            ->orderBy('student_id')
            ->get();

        $dailyData = $studentFees->flatMap(function ($fee) {
            return $fee->payments->map(function ($payment) use ($fee) {
                return [
                    'date' => $payment->payment_date?->format('Y-m-d'),
                    'receipt_number' => $payment->receipt_number,
                    'student_name' => $fee->student->full_name,
                    'admission_no' => $fee->student->admission_no,
                    'class' => $fee->student->schoolClass->name ?? 'N/A',
                    'fee_type' => $fee->feeType->name,
                    'amount_paid' => (float) $payment->amount_paid,
                    'payment_method' => $payment->payment_method,
                    'reference' => $payment->reference,
                    'recorded_by' => $payment->recordedBy->name ?? 'System',
                ];
            });
        })->sortBy('date')->values();

        $summary = [
            'total_collected' => $dailyData->sum('amount_paid'),
            'total_transactions' => $dailyData->count(),
            'by_method' => $dailyData->groupBy('payment_method')->map(fn ($g) => [
                'count' => $g->count(),
                'amount' => $g->sum('amount_paid'),
            ]),
        ];

        if ($request->has('format')) {
            return match ($request->format) {
                'pdf' => $this->exportDailyPdf($dailyData, $summary, $term),
                'csv' => $this->exportDailyCsv($dailyData, $term),
                default => redirect()->back(),
            };
        }

        return view('bursar.daily-collection-report', compact('dailyData', 'summary', 'term'));
    }

    public function outstandingDebtorsReport(Request $request)
    {
        $termId = $request->input('term_id');
        $classId = $request->input('class_id');

        $studentFees = StudentFee::whereIn('status', ['unpaid', 'partial'])
            ->with(['student.schoolClass', 'feeType', 'term', 'payments'])
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->orderBy('student_id')
            ->get();

        $debtors = $studentFees->map(function ($fee) {
            $paid = $fee->payments->sum('amount_paid');
            $expected = $fee->amount_expected;
            $balance = max(0, $expected - $paid);

            return [
                'student_name' => $fee->student->full_name,
                'admission_no' => $fee->student->admission_no,
                'class' => $fee->student->schoolClass->name ?? 'N/A',
                'fee_type' => $fee->feeType->name,
                'term' => $fee->term->name ?? 'N/A',
                'expected' => (float) $expected,
                'paid' => (float) $paid,
                'outstanding' => (float) $balance,
                'status' => $this->feeService->recomputeStatus($fee),
            ];
        });

        $classSummaries = $debtors->groupBy('class')->map(function ($group, $className) {
            return [
                'class' => $className,
                'students' => $group->unique('admission_no')->count(),
                'fee_records' => $group->count(),
                'expected' => $group->sum('expected'),
                'collected' => $group->sum('paid'),
                'outstanding' => $group->sum('outstanding'),
            ];
        })->values();

        $summary = [
            'total_outstanding' => $debtors->sum('outstanding'),
            'total_students' => $debtors->unique('admission_no')->count(),
            'total_fee_records' => $debtors->count(),
        ];

        if ($request->has('format')) {
            return match ($request->format) {
                'pdf' => $this->exportDebtorsPdf($debtors, $classSummaries, $summary),
                'csv' => $this->exportDebtorsCsv($debtors),
                default => redirect()->back(),
            };
        }

        return view('bursar.outstanding-debtors-report', compact('debtors', 'classSummaries', 'summary'));
    }

    public function studentStatement(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $termId = $request->input('term_id');
        $classId = $request->input('class_id');

        $studentFees = StudentFee::where('student_id', $student->id)
            ->with(['feeType', 'term', 'payments'])
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->orderBy('term_id')
            ->get();

        $statement = $studentFees->map(function ($fee) {
            $paid = $fee->payments->sum('amount_paid');
            $expected = $fee->amount_expected;
            $balance = max(0, $expected - $paid);

            return [
                'fee_type' => $fee->feeType->name,
                'term' => $fee->term->name ?? 'N/A',
                'expected' => (float) $expected,
                'paid' => (float) $paid,
                'outstanding' => (float) $balance,
                'status' => $this->feeService->recomputeStatus($fee),
                'payments' => $fee->payments->map(fn ($p) => [
                    'date' => $p->payment_date?->format('Y-m-d'),
                    'receipt_number' => $p->receipt_number,
                    'amount' => (float) $p->amount_paid,
                    'method' => $p->payment_method,
                    'reference' => $p->reference,
                    'recorded_by' => $p->recordedBy->name ?? 'System',
                ]),
            ];
        });

        $summary = [
            'total_expected' => $statement->sum('expected'),
            'total_paid' => $statement->sum('paid'),
            'total_outstanding' => $statement->sum('outstanding'),
        ];

        if ($request->has('format')) {
            return match ($request->format) {
                'pdf' => $this->exportStudentStatementPdf($statement, $summary, $student),
                'csv' => $this->exportStudentStatementCsv($statement, $student),
                default => redirect()->back(),
            };
        }

        return view('bursar.student-statement', compact('student', 'statement', 'summary'));
    }

    private function getDailyCollections(?Term $term): array
    {
        if (! $term) {
            return [];
        }

        return Payment::whereHas('studentFee', fn ($q) => $q->where('term_id', $term->id))
            ->with('studentFee.feeType')
            ->get()
            ->groupBy(fn ($p) => $p->payment_date?->format('Y-m-d'))
            ->map(fn ($payments, $date) => [
                'date' => $date,
                'count' => $payments->count(),
                'amount' => $payments->sum('amount_paid'),
                'by_method' => $payments->groupBy('payment_method')->map(fn ($g) => [
                    'count' => $g->count(),
                    'amount' => $g->sum('amount_paid'),
                ]),
            ])
            ->sortKeysDesc()
            ->values()
            ->toArray();
    }

    private function getPaymentMethodSummary(?Term $term): array
    {
        if (! $term) {
            return [];
        }

        return Payment::whereHas('studentFee', fn ($q) => $q->where('term_id', $term->id))
            ->get()
            ->groupBy('payment_method')
            ->map(fn ($g) => [
                'count' => $g->count(),
                'amount' => $g->sum('amount_paid'),
            ])
            ->toArray();
    }

    private function getOutstandingDebtors(Request $request)
    {
        $termId = $request->input('term_id');
        $classId = $request->input('class_id');

        return StudentFee::whereIn('status', ['unpaid', 'partial'])
            ->with(['student.schoolClass', 'feeType', 'term', 'payments'])
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->orderBy('student_id')
            ->limit(50)
            ->get()
            ->map(function ($fee) {
                $paid = $fee->payments->sum('amount_paid');
                $expected = $fee->amount_expected;

                return [
                    'student_name' => $fee->student->full_name,
                    'admission_no' => $fee->student->admission_no,
                    'class' => $fee->student->schoolClass->name ?? 'N/A',
                    'fee_type' => $fee->feeType->name,
                    'term' => $fee->term->name ?? 'N/A',
                    'expected' => (float) $expected,
                    'paid' => (float) $paid,
                    'outstanding' => max(0, (float) $expected - (float) $paid),
                    'status' => $this->feeService->recomputeStatus($fee),
                ];
            });
    }

    private function exportDailyPdf($dailyData, $summary, $term)
    {
        $pdf = Pdf::loadView('bursar.daily-collection-pdf', compact('dailyData', 'summary', 'term'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("daily-collection-{$term->name}-".now()->format('Y-m-d').'.pdf');
    }

    private function exportDailyCsv($dailyData, $term)
    {
        return $this->csvExportService->exportDailyCollection($dailyData, $term);
    }

    private function exportDebtorsPdf($debtors, $classSummaries, $summary)
    {
        $pdf = Pdf::loadView('bursar.outstanding-debtors-pdf', compact('debtors', 'classSummaries', 'summary'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("outstanding-debtors-".now()->format('Y-m-d').'.pdf');
    }

    private function exportDebtorsCsv($debtors)
    {
        return $this->csvExportService->exportOutstandingDebtors($debtors);
    }

    private function exportStudentStatementPdf($statement, $summary, $student)
    {
        $pdf = Pdf::loadView('bursar.student-statement-pdf', compact('statement', 'summary', 'student'));

        return $pdf->download("statement-{$student->admission_no}-".now()->format('Y-m-d').'.pdf');
    }

    private function exportStudentStatementCsv($statement, $student)
    {
        return $this->csvExportService->exportStudentStatement($statement, $student);
    }
}