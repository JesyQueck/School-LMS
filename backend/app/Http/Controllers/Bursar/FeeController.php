<?php

namespace App\Http\Controllers\Bursar;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordFeePaymentRequest;
use App\Http\Requests\ReversePaymentRequest;
use App\Models\FeePackage;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\SchoolClass;
use App\Models\StudentFee;
use App\Models\Term;
use App\Services\FeeService;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected FeeService $feeService,
    ) {}

    public function index(Request $request)
{
    $validPaidAmount = '(SELECT COALESCE(SUM(payments.amount_paid), 0)
        FROM payments
        WHERE payments.student_fee_id = student_fees.id
        AND NOT EXISTS (
            SELECT 1 FROM payment_reversals
            WHERE payment_reversals.payment_id = payments.id
        ))';

    $studentFees = StudentFee::with(['student.schoolClass', 'feeType', 'term', 'payments', 'componentSnapshots.feeComponent'])
        ->when($request->filled('class_id'), fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $request->class_id)))
        ->when($request->filled('term_id'), fn ($q) => $q->where('term_id', $request->term_id))
        ->when($request->filled('status'), function ($q) use ($request, $validPaidAmount) {
            $status = $request->status;

            if ($status === 'unpaid') {
                $q->whereRaw("$validPaidAmount <= 0");
            } elseif ($status === 'partial') {
                $q->whereRaw("$validPaidAmount > 0 AND $validPaidAmount < student_fees.amount_expected");
            } elseif ($status === 'paid') {
                $q->whereRaw("$validPaidAmount >= student_fees.amount_expected");
            } else {
                $q->whereRaw('1 = 0');
            }
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->whereHas('student', fn ($s) => $s
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('admission_no', 'like', "%{$search}%"));
        })
        ->latest()
        ->paginate(15)
        ->appends($request->only(['class_id', 'term_id', 'status', 'search']));

    $studentFees->each(fn ($fee) => $fee->computed_status = $this->feeService->recomputeStatus($fee));

    return view('bursar.student-fees', [
        'studentFees' => $studentFees,
        'finance' => $this->feeService->financeSummary(),
        'classSummary' => $this->feeService->classSummary(),
        'terms' => Term::all(),
        'classes' => SchoolClass::all(),
        'filters' => $request->only(['class_id', 'term_id', 'status', 'search']),
    ]);
}

    public function showStudentFee(StudentFee $studentFee)
    {
        $studentFee->load(['student.schoolClass', 'feeType', 'term', 'payments.recordedBy', 'componentSnapshots.feeComponent']);

        return view('bursar.student-fee', [
            'studentFee' => $studentFee,
            'status' => $this->feeService->recomputeStatus($studentFee),
            'paid' => $studentFee->payments()->whereDoesntHave('reversal')->sum('amount_paid'),
            'outstanding' => $this->feeService->outstandingBalance($studentFee),
        ]);
    }

    public function createPayment(RecordFeePaymentRequest $request)
    {
        $data = $request->validated();

        $studentFee = StudentFee::with('student')->findOrFail($data['student_fee_id']);

        try {
            $payment = $this->feeService->recordPayment($studentFee, $data, $request->user());
        } catch (\InvalidArgumentException $e) {
    throw \Illuminate\Validation\ValidationException::withMessages([
        'amount_paid' => $e->getMessage(),
    ]);
}

        $this->audit($request, 'payment.created', Payment::class, $payment->id, null, $data);

        return redirect()->route('bursar.student-fees.show', $studentFee)
            ->with('status', 'Payment recorded for '.$studentFee->student->full_name.'.')
            ->with('show_receipt', $payment->id);
    }

    public function reversePayment(ReversePaymentRequest $request)
    {
        $data = $request->validated();

        $payment = Payment::with('studentFee.student')->findOrFail($data['payment_id']);

        try {
            $reversal = $this->feeService->reversePayment($payment, $data['reason'], $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        $this->audit($request, 'payment.reversed', PaymentReversal::class, $reversal->id, null, $data);

        return redirect()->route('bursar.student-fees')
            ->with('status', 'Payment reversed for '.$payment->studentFee->student->full_name.'. Reason: '.$data['reason']);
    }

    public function paymentReceipt(Request $request, Payment $payment)
    {
        $payment->load(['studentFee.student.schoolClass', 'studentFee.feeType', 'studentFee.term', 'recordedBy', 'reversal']);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('bursar.receipt-pdf', compact('payment'));

            return $pdf->download("receipt-{$payment->receipt_number}.pdf");
        }

        return view('bursar.receipt', compact('payment'));
    }

    public function searchStudentFees(Request $request): JsonResponse
{
    $validPaidAmount = '(SELECT COALESCE(SUM(payments.amount_paid), 0)
        FROM payments
        WHERE payments.student_fee_id = student_fees.id
        AND NOT EXISTS (
            SELECT 1 FROM payment_reversals
            WHERE payment_reversals.payment_id = payments.id
        ))';

    $results = StudentFee::with(['student.schoolClass', 'feeType'])
        ->whereRaw("$validPaidAmount < student_fees.amount_expected")
        ->when($request->filled('q'), function ($q) use ($request) {
            $search = $request->input('q');

            $search = trim($search);

            $q->whereHas('student', function ($s) use ($search) {
                $s->where('first_name', 'like', "{$search}%")
                    ->orWhere('middle_name', 'like', "{$search}%")
                    ->orWhere('last_name', 'like', "{$search}%")
                    ->orWhere('admission_no', 'like', "{$search}%");
            });
        })
        ->limit(20)
        ->get()
        ->map(fn ($fee) => [
            'id' => $fee->id,
            'label' => $fee->student->full_name.' - '.$fee->feeName,
            'student_name' => $fee->student->full_name,
            'fee_type' => $fee->feeName,
            'amount_expected' => $fee->amount_expected,
            'class' => $fee->student->schoolClass?->name,
            'admission_no' => $fee->student->admission_no,
        ]);

    return response()->json($results);
}

    public function exportFinancialReport(Request $request)
    {
        $termId = $request->input('term_id');
        $classId = $request->input('class_id');
        $session = $request->input('academic_session_id');

        $studentFees = StudentFee::with(['student.schoolClass', 'feeType', 'term', 'payments'])
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->when($session, function ($q) use ($session) {
                $q->whereHas('term', fn ($t) => $t->where('academic_session_id', $session));
            })
            ->orderBy('student_id')
            ->get();

        $reportData = $studentFees->map(function ($fee) {
            $paid = $fee->payments()->whereDoesntHave('reversal')->sum('amount_paid');
            $expected = $fee->amount_expected;

            return [
                'student_name' => $fee->student->full_name,
                'admission_no' => $fee->student->admission_no,
                'class' => $fee->student->schoolClass->name ?? 'N/A',
                'class_id' => $fee->student->schoolClass->id ?? null,
                'fee_type' => $fee->feeType->name,
                'term' => $fee->term->name ?? 'N/A',
                'expected' => (float) $expected,
                'expected_formatted' => '₦'.number_format($expected, 2),
                'paid' => (float) $paid,
                'paid_formatted' => '₦'.number_format($paid, 2),
                'outstanding' => max(0, (float) $expected - (float) $paid),
                'outstanding_formatted' => '₦'.number_format(max(0, $expected - $paid), 2),
                'status' => $this->feeService->recomputeStatus($fee),
            ];
        });

        $classSummaries = $reportData->groupBy('class')->map(function ($group, $className) {
            $expected = $group->sum('expected');
            $collected = $group->sum('paid');
            $outstanding = $group->sum('outstanding');

            return [
                'class' => $className,
                'students' => $group->count(),
                'fees' => $group->count(),
                'expected' => $expected,
                'expected_formatted' => '₦'.number_format($expected, 2),
                'collected' => $collected,
                'collected_formatted' => '₦'.number_format($collected, 2),
                'outstanding' => $outstanding,
                'outstanding_formatted' => '₦'.number_format($outstanding, 2),
                'collection_rate' => $expected > 0 ? round(($collected / $expected) * 100, 1) : 0,
                'records' => $group,
            ];
        })->values();

        $summary = [
            'expected' => $reportData->sum('expected'),
            'collected' => $reportData->sum('paid'),
            'outstanding' => $reportData->sum('outstanding'),
            'total_fees' => $reportData->count(),
        ];

        $pdf = Pdf::loadView('admin.finance.financial-report', compact('classSummaries', 'summary', 'termId', 'classId'));

        return $pdf->download('financial-report-'.now()->format('Y-m-d').'.pdf');
    }
}