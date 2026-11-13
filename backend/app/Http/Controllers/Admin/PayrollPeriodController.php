<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayrollPeriodRequest;
use App\Http\Requests\UpdatePayrollPeriodRequest;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $query = PayrollPeriod::withCount('payrollRecords')->orderByDesc('start_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $periods = $query->paginate(15)->withQueryString();

        return view('admin.payroll.periods.index', [
            'periods' => $periods,
            'statuses' => PayrollPeriod::STATUSES,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        return view('admin.payroll.periods.create', [
            'statuses' => PayrollPeriod::STATUSES,
            'users' => User::where('role', 'admin')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePayrollPeriodRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $period = PayrollPeriod::create($data);

        $this->audit($request, 'payroll_period.created', PayrollPeriod::class, $period->id, null, $data);

        return redirect()->route('admin.payroll.periods.show', $period)
            ->with('status', 'Payroll period created.');
    }

    public function show(PayrollPeriod $period)
    {
        $period->load(['payrollRecords.employee', 'payrollRecords.components', 'creator']);

        $calculated = $period->payrollRecords->where('status', PayrollRecord::STATUS_CALCULATED);
        $approved = $period->payrollRecords->where('status', PayrollRecord::STATUS_APPROVED);
        $paid = $period->payrollRecords->where('status', PayrollRecord::STATUS_PAID);
        $voided = $period->payrollRecords->where('status', PayrollRecord::STATUS_VOIDED);

        return view('admin.payroll.periods.show', [
            'period' => $period,
            'calculated' => $calculated,
            'approved' => $approved,
            'paid' => $paid,
            'voided' => $voided,
        ]);
    }

    public function edit(PayrollPeriod $period)
    {
        return view('admin.payroll.periods.edit', [
            'period' => $period,
            'statuses' => PayrollPeriod::STATUSES,
        ]);
    }

    public function update(UpdatePayrollPeriodRequest $request, PayrollPeriod $period)
    {
        $old = $period->getDirty();
        $data = $request->validated();

        $period->update($data);

        $this->audit($request, 'payroll_period.updated', PayrollPeriod::class, $period->id, $old, $data);

        return redirect()->route('admin.payroll.periods.show', $period)
            ->with('status', 'Payroll period updated.');
    }

    public function destroy(Request $request, PayrollPeriod $period)
    {
        if ($period->payrollRecords()->exists()) {
            return back()->withErrors(['period' => 'Cannot delete a payroll period that has payroll records.']);
        }

        $period->delete();

        $this->audit($request, 'payroll_period.deleted', PayrollPeriod::class, $period->id, null, null);

        return redirect()->route('admin.payroll.periods.index')
            ->with('status', 'Payroll period deleted.');
    }
}
