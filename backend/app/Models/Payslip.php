<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_record_id',
        'payslip_number',
        'generated_at',
        'generated_by',
        'pdf_path',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
