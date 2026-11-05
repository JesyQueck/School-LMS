<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeeComponentSnapshot extends Model
{
    protected $fillable = [
        'student_fee_id',
        'fee_component_id',
        'component_name',
        'component_amount',
        'component_description',
    ];

    protected $casts = [
        'component_amount' => 'decimal:2',
    ];

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class, 'student_fee_id');
    }

    public function feeComponent(): BelongsTo
    {
        return $this->belongsTo(FeeComponent::class, 'fee_component_id');
    }
}