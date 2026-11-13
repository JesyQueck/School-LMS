<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\PayrollRecordComponent;
use App\Models\SalaryComponent;
use App\Models\SalaryProfile;
use App\Models\SalaryProfileComponent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public const STATUS_CALCULATED = 'calculated';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_VOIDED = 'voided';

    public const COMPONENT_TYPE_ALLOWANCE = 'allowance';
    public const COMPONENT_TYPE_DEDUCTION = 'deduction';

    public const CALC_FIXED = 'fixed';
    public const CALC_PERCENTAGE = 'percentage';

    /**
     * Generate payroll records for all eligible employees for a given period.
     *
     * @return array{records: list<PayrollRecord>, excluded: list<array{employee: Employee, reason: string}>}
     */
    public function generatePayroll(PayrollPeriod $period, User $generatedBy): array
    {
        if (! in_array($period->status, [PayrollPeriod::STATUS_DRAFT, PayrollPeriod::STATUS_PROCESSED], true)) {
            throw new \InvalidArgumentException('Payroll can only be generated for draft or processed periods.');
        }

        $employees = Employee::where('employment_status', Employee::STATUS_ACTIVE)->orderBy('employee_number')->get();

        $records = [];
        $excluded = [];

        DB::transaction(function () use ($period, $generatedBy, $employees, &$records, &$excluded) {
            foreach ($employees as $employee) {
                // Prevent duplicate payroll records for the same employee and period.
                if ($employee->payrollRecords()->where('payroll_period_id', $period->id)->exists()) {
                    $excluded[] = [
                        'employee' => $employee,
                        'reason' => 'A payroll record already exists for this employee and period.',
                    ];

                    continue;
                }

                $profile = $this->resolveEffectiveProfile($employee, $period);

                if (! $profile) {
                    $excluded[] = [
                        'employee' => $employee,
                        'reason' => 'No active salary profile effective for this payroll period.',
                    ];

                    continue;
                }

                $calculation = $this->calculatePayroll($profile, $period);

                $record = PayrollRecord::create([
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $period->id,
                    'basic_salary_snapshot' => $calculation['basic_salary'],
                    'gross_pay' => $calculation['gross_pay'],
                    'total_allowances' => $calculation['total_allowances'],
                    'total_deductions' => $calculation['total_deductions'],
                    'net_pay' => $calculation['net_pay'],
                    'status' => self::STATUS_CALCULATED,
                ]);

                foreach ($calculation['components'] as $component) {
                    PayrollRecordComponent::create([
                        'payroll_record_id' => $record->id,
                        'salary_component_id' => $component['salary_component_id'],
                        'component_name' => $component['component_name'],
                        'component_type' => $component['component_type'],
                        'amount' => $component['amount'],
                        'calculation_basis' => $component['calculation_basis'],
                        'calculation_value' => $component['calculation_value'],
                    ]);
                }

                $records[] = $record;
            }
        });

        return [
            'records' => $records,
            'excluded' => $excluded,
        ];
    }

    /**
     * Calculate payroll values from a salary profile without persisting.
     *
     * @return array{
     *     basic_salary: float,
     *     total_allowances: float,
     *     total_deductions: float,
     *     gross_pay: float,
     *     net_pay: float,
     *     components: list<array{
     *         salary_component_id: int|null,
     *         component_name: string,
     *         component_type: string,
     *         amount: float,
     *         calculation_basis: string,
     *         calculation_value: float,
     *     }>
     * }
     */
    public function calculatePayroll(SalaryProfile $profile, PayrollPeriod $period): array
    {
        $basicSalary = (float) $profile->basic_salary;

        $allowances = [];
        $deductions = [];

        foreach ($profile->components as $profileComponent) {
            if (! $this->isComponentEffective($profileComponent, $period)) {
                continue;
            }

            $component = $profileComponent->salaryComponent;
            if (! $component || ! $component->is_active) {
                continue;
            }

            $calculationType = $component->calculation_type ?? self::CALC_FIXED;
            $configuredValue = (float) ($profileComponent->configured_value ?? $component->default_value ?? 0);

            $amount = match ($calculationType) {
                self::CALC_PERCENTAGE => round(($basicSalary / 100.0) * $configuredValue, 2),
                default => round($configuredValue, 2),
            };

            $entry = [
                'salary_component_id' => $component->id,
                'component_name' => $component->name,
                'component_type' => $component->component_type,
                'amount' => $amount,
                'calculation_basis' => $calculationType,
                'calculation_value' => $configuredValue,
            ];

            if ($component->component_type === self::COMPONENT_TYPE_ALLOWANCE) {
                $allowances[] = $entry;
            } else {
                $deductions[] = $entry;
            }
        }

        $totalAllowances = round(array_sum(array_column($allowances, 'amount')), 2);
        $totalDeductions = round(array_sum(array_column($deductions, 'amount')), 2);
        $grossPay = round($basicSalary + $totalAllowances, 2);
        $netPay = round($grossPay - $totalDeductions, 2);

        return [
            'basic_salary' => $basicSalary,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'components' => array_merge($allowances, $deductions),
        ];
    }

    /**
     * Approve a calculated payroll record. Only admins may approve.
     */
    public function approve(PayrollRecord $record, User $approvedBy): void
    {
        if ($record->status !== self::STATUS_CALCULATED) {
            throw new \InvalidArgumentException('Only calculated payroll records can be approved.');
        }

        if ($record->payrollPeriod->status === PayrollPeriod::STATUS_PAID || $record->payrollPeriod->status === PayrollPeriod::STATUS_LOCKED) {
            throw new \InvalidArgumentException('Cannot approve payroll for a paid or locked period.');
        }

        DB::transaction(function () use ($record, $approvedBy) {
            $record->update([
                'status' => self::STATUS_APPROVED,
                'approved_by' => $approvedBy->id,
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Reject (void) a calculated or approved payroll record. Only admins may reject.
     */
    public function reject(PayrollRecord $record, User $rejectedBy): void
    {
        if (! in_array($record->status, [self::STATUS_CALCULATED, self::STATUS_APPROVED], true)) {
            throw new \InvalidArgumentException('Only calculated or approved payroll records can be rejected.');
        }

        if ($record->status === self::STATUS_PAID) {
            throw new \InvalidArgumentException('Paid payroll records cannot be rejected.');
        }

        DB::transaction(function () use ($record) {
            $record->update(['status' => self::STATUS_VOIDED]);
        });
    }

    /**
     * Record payment for an approved payroll record. Only bursars may record payments.
     */
    public function recordPayment(PayrollRecord $record, array $data, User $paidBy): void
    {
        if ($record->status !== self::STATUS_APPROVED) {
            throw new \InvalidArgumentException('Only approved payroll records can be paid.');
        }

        if ($record->payrollPeriod->status === PayrollPeriod::STATUS_PAID || $record->payrollPeriod->status === PayrollPeriod::STATUS_LOCKED) {
            throw new \InvalidArgumentException('Cannot record payment for a paid or locked period.');
        }

        $paymentMethod = $data['payment_method'] ?? 'bank_transfer';
        $paymentReference = $data['payment_reference'] ?? null;
        $paidAt = $data['paid_at'] ?? now();

        DB::transaction(function () use ($record, $paymentMethod, $paymentReference, $paidAt, $paidBy) {
            // Re-check status inside the transaction to prevent duplicate payment recording.
            $record->refresh();

            if ($record->status !== self::STATUS_APPROVED) {
                throw new \InvalidArgumentException('This payroll record has already been paid or voided.');
            }

            $record->update([
                'status' => self::STATUS_PAID,
                'paid_by' => $paidBy->id,
                'paid_at' => $paidAt,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
            ]);
        });
    }

    /**
     * Resolve the active salary profile effective for the given payroll period.
     *
     * A profile is effective if it is active and its date range overlaps
     * the payroll period. When multiple profiles overlap, the one whose
     * effective_from is latest and on or before the period start wins;
     * otherwise the profile active on the period start date is used.
     */
    protected function resolveEffectiveProfile(Employee $employee, PayrollPeriod $period): ?SalaryProfile
    {
        return $employee->salaryProfiles()
            ->where('is_active', true)
            ->where(function ($q) use ($period) {
                // Profile overlaps the payroll period.
                $q->whereDate('effective_from', '<=', $period->end_date)
                    ->where(function ($inner) use ($period) {
                        $inner->whereNull('effective_to')
                            ->orWhereDate('effective_to', '>=', $period->start_date);
                    });
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Determine whether a salary profile component is effective for the payroll period.
     *
     * A component with no effective_from is treated as always effective.
     * A component is effective if its date range overlaps the payroll period.
     */
    protected function isComponentEffective(SalaryProfileComponent $profileComponent, PayrollPeriod $period): bool
    {
        $effectiveFrom = $profileComponent->effective_from;
        $effectiveTo = $profileComponent->effective_to;

        if (! $effectiveFrom) {
            return true;
        }

        $from = \Carbon\Carbon::parse($effectiveFrom);
        $to = $effectiveTo ? \Carbon\Carbon::parse($effectiveTo) : null;

        if ($from->gt($period->end_date)) {
            return false;
        }

        if ($to && $to->lt($period->start_date)) {
            return false;
        }

        return true;
    }
}
