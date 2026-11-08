<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRecord extends Model
{
    public const STATUS_CALCULATED = 'calculated';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_VOIDED = 'voided';

    public const STATUSES = [
        self::STATUS_CALCULATED,
        self::STATUS_APPROVED,
        self::STATUS_PAID,
        self::STATUS_VOIDED,
    ];

    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'basic_salary_snapshot',
        'gross_pay',
        'total_allowances',
        'total_deductions',
        'net_pay',
        'status',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'basic_salary_snapshot' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function components(): HasMany
    {
        return $this->hasMany(PayrollRecordComponent::class);
    }

    public function payslip(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payslip::class);
    }
}
