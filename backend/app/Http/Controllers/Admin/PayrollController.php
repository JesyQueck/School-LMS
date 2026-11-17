<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovePayrollRequest;
use App\Http\Requests\StorePayrollPeriodRequest;
use App\Http\Requests\UpdatePayrollPeriodRequest;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
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
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('employment_status', Employee::STATUS_ACTIVE)->count();
        $currentPeriod = PayrollPeriod::where('status', PayrollPeriod::STATUS_DRAFT)
            ->orderBy('start_date', 'desc')
            ->first() ?? PayrollPeriod::orderBy('start_date', 'desc')->first();

        $totalPayrollAmount = $currentPeriod
            ? PayrollRecord::where('payroll_period_id', $currentPeriod->id)
                ->where('status', '!=', PayrollRecord::STATUS_VOIDED)
                ->sum('net_pay')
            : 0;

        $pendingApprovals = $currentPeriod
        ? PayrollRecord::where('payroll_period_id', $currentPeriod->id)
            ->where('status', PayrollRecord::STATUS_CALCULATED)
            ->count()
        : 0;

        $recentlyProcessed = PayrollRecord::with(['employee', 'period'])
            ->whereIn('status', [PayrollRecord::STATUS_APPROVED, PayrollRecord::STATUS_PAID])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.payroll.index', [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'currentPeriod' => $currentPeriod,
            'totalPayrollAmount' => $totalPayrollAmount,
            'pendingApprovals' => $pendingApprovals,
            'recentlyProcessed' => $recentlyProcessed,
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
            return back()->with('status', 'Payroll generated for '.count($result['records']).' employees. '.count($result['excluded']).' employee(s) excluded (see details).')
                ->with('excluded', $result['excluded']);
        }

        return back()->with('status', 'Payroll generated for '.count($result['records']).' employees.');
    }

    public function history(Request $request)
    {
        $payrolls = PayrollRecord::with(['employee', 'period', 'approver', 'payer'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.payroll.history', [
            'payrolls' => $payrolls,
        ]);
    }

    public function show(PayrollRecord $payrollRecord)
    {
        $payrollRecord->load(['employee', 'period', 'components.salaryComponent', 'approver', 'payer', 'payslip']);

        return view('admin.payroll.show', [
            'payrollRecord' => $payrollRecord,
        ]);
    }

    public function approve(ApprovePayrollRequest $request, PayrollRecord $payrollRecord)
    {
        try {
            $this->payrollService->approve($payrollRecord, $request->user());

            $this->audit($request, 'payroll.approved', PayrollRecord::class, $payrollRecord->id, null, [
                'record_id' => $payrollRecord->id,
                'employee' => $payrollRecord->employee->employee_number,
                'period' => $payrollRecord->period->name,
                'net_pay' => $payrollRecord->net_pay,
            ]);

            return back()->with('status', 'Payroll approved for '.$payrollRecord->employee->employee_number.'.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payroll' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, PayrollRecord $payrollRecord)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->payrollService->reject($payrollRecord, $request->user());

            $this->audit($request, 'payroll.rejected', PayrollRecord::class, $payrollRecord->id, null, [
                'record_id' => $payrollRecord->id,
                'employee' => $payrollRecord->employee->employee_number,
                'period' => $payrollRecord->period->name,
                'reason' => $request->reason,
            ]);

            return back()->with('status', 'Payroll rejected for '.$payrollRecord->employee->employee_number.'.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payroll' => $e->getMessage()]);
        }
    }

    public function payslip(Request $request, PayrollRecord $payrollRecord)
    {
        abort_unless(
            in_array($payrollRecord->status, [
                PayrollRecord::STATUS_APPROVED,
                PayrollRecord::STATUS_PAID,
            ], true),
            404
        );

        $payrollRecord->load([
            'employee.user',
            'period',
            'components',
            'payslip.generator',
        ]);

        if ($request->boolean('download')) {
            $pdf = Pdf::loadView(
                'bursar.payroll.payslip-pdf',
                compact('payrollRecord')
            );

            return $pdf->download(
                'payslip-'.$payrollRecord->employee->employee_number.'-'.$payrollRecord->period->name.'.pdf'
            );
        }

        return view('admin.payroll.payslip', [
            'payrollRecord' => $payrollRecord,
        ]);
    }
}
