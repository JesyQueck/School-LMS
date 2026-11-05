<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeTypeRequest;
use App\Models\AcademicSession;
use App\Models\FeePackage;
use App\Models\FeePackageAssignment;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StudentFee;
use App\Models\Term;
use App\Services\FeeService;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected FeeService $feeService,
    ) {}

    public function index(Request $request)
    {
        $feeTypes = FeeType::with(['term', 'class'])->latest()->paginate(15);
        $feePackages = FeePackage::with('components')->latest()->paginate(15);

        return view('admin.finance.index', [
            'finance' => $this->feeService->financeSummary(),
            'classSummary' => $this->feeService->classSummary(),
            'feeTypes' => $feeTypes,
            'feePackages' => $feePackages,
            'terms' => Term::all(),
            'classes' => SchoolClass::all(),
            'academicSessions' => AcademicSession::all(),
        ]);
    }

    public function showStudentFee(StudentFee $studentFee)
    {
        $studentFee->load(['student.schoolClass', 'feeType', 'term', 'payments.recordedBy', 'componentSnapshots.feeComponent']);

        return view('admin.finance.student-fee', [
            'studentFee' => $studentFee,
            'status' => $this->feeService->recomputeStatus($studentFee),
            'paid' => $studentFee->payments()->whereDoesntHave('reversal')->sum('amount_paid'),
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