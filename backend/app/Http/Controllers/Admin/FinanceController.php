<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeTypeRequest;
use App\Http\Requests\StoreStudentFeePaymentRequest;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StudentFee;
use App\Models\Term;
use App\Services\FeeService;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected FeeService $feeService,
    ) {}

    public function index(Request $request)
    {
        $studentFees = StudentFee::with(['student', 'feeType', 'term', 'payments'])
            ->when($request->filled('class_id'), fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $request->class_id)))
            ->when($request->filled('term_id'), fn ($q) => $q->where('term_id', $request->term_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('student', fn ($s) => $s
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_no', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->appends($request->only(['class_id', 'term_id', 'status', 'search']));

        if ($request->filled('status')) {
            $status = $request->status;
            $studentFees = $studentFees->filter(fn ($fee) => $this->feeService->recomputeStatus($fee) === $status)->values();
        }

        $studentFees->each(fn ($fee) => $fee->computed_status = $this->feeService->recomputeStatus($fee));

        $payments = Payment::with(['studentFee.student', 'studentFee.feeType', 'recordedBy'])
            ->latest('payment_date')
            ->paginate(10)
            ->appends($request->only(['class_id', 'term_id', 'status', 'search']));

        return view('admin.finance.index', [
            'finance' => $this->feeService->financeSummary(),
            'classSummary' => $this->feeService->classSummary(),
            'feeTypes' => FeeType::with(['term', 'class'])->get(),
            'studentFees' => $studentFees,
            'payments' => $payments,
            'unpaidFees' => StudentFee::whereIn('status', ['unpaid', 'partial'])
                ->with(['student', 'feeType'])
                ->latest()
                ->limit(10)
                ->get(),
            'terms' => Term::all(),
            'classes' => SchoolClass::all(),
            'filters' => $request->only(['class_id', 'term_id', 'status', 'search']),
        ]);
    }

    public function showStudentFee(StudentFee $studentFee)
    {
        $studentFee->load(['student.schoolClass', 'feeType', 'term', 'payments.recordedBy']);

        return view('admin.finance.student-fee', [
            'studentFee' => $studentFee,
            'status' => $this->feeService->recomputeStatus($studentFee),
            'paid' => $studentFee->payments->sum('amount_paid'),
            'outstanding' => $this->feeService->outstandingBalance($studentFee),
        ]);
    }

    public function createFeeType(StoreFeeTypeRequest $request)
    {
        $data = $request->validated();

        $feeType = FeeType::create($data);

        $this->feeService->createStudentFeesForFeeType($feeType);

        $this->audit($request, 'fee_type.created', FeeType::class, $feeType->id, null, $data);

        return redirect()->route('admin.finance')->with('status', 'Fee type created. Student fees generated for '.($feeType->class ? $feeType->class->name : 'all classes').'.');
    }

    public function createStudentFeePayment(StoreStudentFeePaymentRequest $request)
    {
        $data = $request->validated();

        $studentFee = StudentFee::with('student')->findOrFail($data['student_fee_id']);

        try {
            $payment = $this->feeService->recordPayment($studentFee, $data, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount_paid' => $e->getMessage()])->withInput();
        }

        $this->audit($request, 'payment.created', Payment::class, $payment->id, null, $data);

        return redirect()->route('admin.finance')->with('status', 'Payment recorded for '.$studentFee->student->full_name.'.');
    }

    public function paymentReceipt(Request $request, Payment $payment)
    {
        $payment->load(['studentFee.student.schoolClass', 'studentFee.feeType', 'studentFee.term', 'recordedBy']);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('admin.finance.receipt-pdf', compact('payment'));

            return $pdf->download("receipt-{$payment->receipt_number}.pdf");
        }

        return view('admin.finance.receipt', compact('payment'));
    }

    public function searchStudentFees(Request $request): JsonResponse
    {
        $results = StudentFee::with(['student.schoolClass', 'feeType'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->input('q');
                $q->whereHas('student', function ($s) use ($search) {
                    $s->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get()
            ->map(fn ($fee) => [
                'id' => $fee->id,
                'label' => $fee->student->full_name.' - '.$fee->feeType->name.' ('.$fee->student->admission_no.')',
                'student_name' => $fee->student->full_name,
                'fee_type' => $fee->feeType->name,
                'amount_expected' => number_format($fee->amount_expected, 2),
                'class' => $fee->student->schoolClass->name ?? 'N/A',
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
            $paid = $fee->payments->sum('amount_paid');
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
