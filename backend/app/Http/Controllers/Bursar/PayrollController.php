<?php

namespace App\Http\Controllers\Bursar;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordPayrollPaymentRequest;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Services\PayrollService;
use App\Traits\AuditsActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected PayrollService $payrollService,
    ) {}

    public function index(Request $request)
    {
        $employees = Employee::where('employment_status', Employee::STATUS_ACTIVE)
            ->orderBy('employee_number')
            ->get();

        $periods = PayrollPeriod::orderByDesc('start_date')->get();

        $payrollRecords = PayrollRecord::with(['employee', 'period'])
            ->whereIn('status', [PayrollRecord::STATUS_APPROVED, PayrollRecord::STATUS_PAID])
            ->latest()
            ->limit(10)
            ->get();

        return view('bursar.staff-salaries', [
            'employees' => $employees,
            'periods' => $periods,
            'recentRecords' => $payrollRecords,
        ]);
    }

    public function employees(Request $request)
    {
        $query = Employee::where('employment_status', Employee::STATUS_ACTIVE)->orderBy('employee_number');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(15)->withQueryString();

        return view('bursar.payroll.employees', [
            'employees' => $employees,
            'filters' => $request->only(['search']),
        ]);
    }

    public function periods(Request $request)
    {
        $periods = PayrollPeriod::orderByDesc('start_date')->paginate(15)->withQueryString();

        return view('bursar.payroll.periods', [
            'periods' => $periods,
        ]);
    }

    public function prepare(Request $request, PayrollPeriod $period)
    {
        $employees = Employee::where('employment_status', Employee::STATUS_ACTIVE)
            ->whereDoesntHave('payrollRecords', fn ($q) => $q->where('payroll_period_id', $period->id))
            ->orderBy('employee_number')
            ->get();

        return view('bursar.payroll.prepare', [
            'period' => $period,
            'employees' => $employees,
        ]);
    }

    public function generate(Request $request, PayrollPeriod $period)
    {
        $result = $this->payrollService->generatePayroll($period, $request->user());

        $this->audit($request, 'payroll.generated', PayrollPeriod::class, $period->id, null, [
            'period' => $period->name,
            'records_generated' => count($result['records']),
            'excluded' => count($result['excluded']),
        ]);

        if ($result['excluded']) {
            return back()->with('status', 'Payroll prepared for '.count($result['records']).' employees. '.count($result['excluded']).' excluded.')
                ->with('excluded', $result['excluded']);
        }

        return back()->with('status', 'Payroll prepared for '.count($result['records']).' employees.');
    }

    public function records(Request $request, PayrollPeriod $period)
    {
        $query = PayrollRecord::with(['employee', 'components'])
            ->where('payroll_period_id', $period->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        return view('bursar.payroll.records', [
            'period' => $period,
            'records' => $records,
            'statuses' => PayrollRecord::STATUSES,
            'filters' => $request->only(['status']),
        ]);
    }

    public function breakdown(PayrollRecord $payrollRecord)
    {
        $payrollRecord->load(['employee', 'period', 'components.salaryComponent', 'payslip']);

        return view('bursar.payroll.breakdown', [
            'payrollRecord' => $payrollRecord,
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $query = PayrollRecord::with(['employee', 'period', 'payer'])
            ->where('status', PayrollRecord::STATUS_PAID)
            ->latest();

        $records = $query->paginate(15)->withQueryString();

        return view('bursar.payroll.payment-history', [
            'records' => $records,
        ]);
    }

    public function recordPayment(RecordPayrollPaymentRequest $request, PayrollRecord $payrollRecord)
    {
        try {
            $this->payrollService->recordPayment($payrollRecord, $request->validatedData(), $request->user());

            $this->audit($request, 'payroll.payment_recorded', PayrollRecord::class, $payrollRecord->id, null, [
                'record_id' => $payrollRecord->id,
                'employee' => $payrollRecord->employee->employee_number,
                'period' => $payrollRecord->period->name,
                'payment_method' => $request->validated('payment_method'),
                'payment_reference' => $request->validated('payment_reference'),
            ]);

            return back()->with('status', 'Payment recorded for '.$payrollRecord->employee->employee_number.'.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payroll' => $e->getMessage()]);
        }
    }

    public function payslip(Request $request, PayrollRecord $payrollRecord)
    {
        $payrollRecord->load(['employee', 'period', 'components', 'payslip.generator']);

        if ($request->has('download')) {
            $pdf = Pdf::loadView('bursar.payroll.payslip-pdf', compact('payrollRecord'));

            return $pdf->download('payslip-'.$payrollRecord->employee->employee_number.'-'.$payrollRecord->period->name.'.pdf');
        }

        return view('bursar.payroll.payslip', [
            'payrollRecord' => $payrollRecord,
        ]);
    }
}
