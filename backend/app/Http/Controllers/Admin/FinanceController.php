<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use App\Services\FeeService;
use App\Traits\AuditsActions;
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
            ->get();

        if ($request->filled('status')) {
            $status = $request->status;
            $studentFees = $studentFees->filter(fn ($fee) => $this->feeService->recomputeStatus($fee) === $status)->values();
        }

        $studentFees->each(fn ($fee) => $fee->computed_status = $this->feeService->recomputeStatus($fee));

        $payments = Payment::with(['studentFee.student', 'studentFee.feeType', 'recordedBy'])
            ->latest('payment_date')
            ->get();

        return view('admin.finance.index', [
            'finance' => $this->feeService->financeSummary(),
            'feeTypes' => FeeType::with(['term', 'class'])->get(),
            'studentFees' => $studentFees,
            'payments' => $payments,
            'students' => Student::with('class')->get(),
            'terms' => Term::all(),
            'classes' => SchoolClass::all(),
            'filters' => $request->only(['class_id', 'term_id', 'status', 'search']),
        ]);
    }

    public function showStudentFee(StudentFee $studentFee)
    {
        $studentFee->load(['student.class', 'feeType', 'term', 'payments.recordedBy']);

        return view('admin.finance.student-fee', [
            'studentFee' => $studentFee,
            'status' => $this->feeService->recomputeStatus($studentFee),
            'paid' => $studentFee->payments->sum('amount_paid'),
            'outstanding' => $this->feeService->outstandingBalance($studentFee),
        ]);
    }

    public function createFeeType(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $feeType = FeeType::create($data);

        $this->audit($request, 'fee_type.created', FeeType::class, $feeType->id, null, $data);

        return redirect()->route('admin.finance')->with('status', 'Fee type created.');
    }

    public function createStudentFee(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'amount_expected' => ['required', 'numeric'],
        ]);

        $studentFee = StudentFee::create($data);
        $studentFee->update(['status' => $this->feeService->recomputeStatus($studentFee)]);

        $this->audit($request, 'student_fee.created', StudentFee::class, $studentFee->id, null, $studentFee->toArray());

        return redirect()->route('admin.finance')->with('status', 'Student fee created.');
    }

    public function createPayment(Request $request)
    {
        $data = $request->validate([
            'student_fee_id' => ['required', 'exists:student_fees,id'],
            'receipt_number' => ['nullable', 'string', 'max:255'],
            'amount_paid' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $studentFee = StudentFee::findOrFail($data['student_fee_id']);

        try {
            $payment = $this->feeService->recordPayment($studentFee, $data, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount_paid' => $e->getMessage()])->withInput();
        }

        $this->audit($request, 'payment.created', Payment::class, $payment->id, null, $data);

        return redirect()->route('admin.finance')->with('status', 'Payment recorded.');
    }

    public function paymentReceipt(Request $request, Payment $payment)
    {
        $payment->load(['studentFee.student.class', 'studentFee.feeType', 'studentFee.term', 'recordedBy']);

        if ($request->has('download')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.receipt-pdf', compact('payment'));
            return $pdf->download("receipt-{$payment->receipt_number}.pdf");
        }

        return view('admin.finance.receipt', compact('payment'));
    }
}
