<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecordComponent extends Model
{
    protected $fillable = [
        'payroll_record_id',
        'salary_component_id',
        'component_name',
        'component_type',
        'amount',
        'calculation_basis',
        'calculation_value',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'calculation_value' => 'decimal:2',
    ];

    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class);
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
