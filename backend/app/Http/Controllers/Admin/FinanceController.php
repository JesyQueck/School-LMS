<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeTypeRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreStudentFeeRequest;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use App\Services\FeeService;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->paginate(20)
            ->appends($request->only(['class_id', 'term_id', 'status', 'search']));

        if ($request->filled('status')) {
            $status = $request->status;
            $studentFees = $studentFees->filter(fn ($fee) => $this->feeService->recomputeStatus($fee) === $status)->values();
        }

        $studentFees->each(fn ($fee) => $fee->computed_status = $this->feeService->recomputeStatus($fee));

        $payments = Payment::with(['studentFee.student', 'studentFee.feeType', 'recordedBy'])
            ->latest('payment_date')
            ->paginate(20)
            ->appends($request->only(['class_id', 'term_id', 'status', 'search']));

        return view('admin.finance.index', [
            'finance' => $this->feeService->financeSummary(),
            'feeTypes' => FeeType::with(['term', 'class'])->get(),
            'studentFees' => $studentFees,
            'payments' => $payments,
            'students' => Student::with('schoolClass')->get(),
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

        $this->audit($request, 'fee_type.created', FeeType::class, $feeType->id, null, $data);

        return redirect()->route('admin.finance')->with('status', 'Fee type created.');
    }

    public function createStudentFee(StoreStudentFeeRequest $request)
    {
        $data = $request->validated();

        $class = SchoolClass::findOrFail($data['class_id']);

        DB::transaction(function () use ($class, $data, $request) {
            $class->students()->each(function ($student) use ($data, $request) {
                $studentFee = StudentFee::create([
                    'student_id' => $student->id,
                    'fee_type_id' => $data['fee_type_id'],
                    'term_id' => $data['term_id'],
                    'amount_expected' => $data['amount_expected'],
                    'status' => $this->feeService->recomputeStatus(StudentFee::make(['amount_expected' => $data['amount_expected']])),
                ]);

                $this->audit($request, 'student_fee.created', StudentFee::class, $studentFee->id, null, $studentFee->toArray());
            });
        });

        return redirect()->route('admin.finance')->with('status', 'Fee assigned to all students in '.$class->name.'.');
    }

    public function createPayment(StorePaymentRequest $request)
    {
        $data = $request->validated();

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
        $payment->load(['studentFee.student.schoolClass', 'studentFee.feeType', 'studentFee.term', 'recordedBy']);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('admin.finance.receipt-pdf', compact('payment'));

            return $pdf->download("receipt-{$payment->receipt_number}.pdf");
        }

        return view('admin.finance.receipt', compact('payment'));
    }
}
