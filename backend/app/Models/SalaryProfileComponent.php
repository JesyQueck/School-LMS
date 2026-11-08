<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryProfileComponent extends Model
{
    protected $fillable = [
        'salary_profile_id',
        'salary_component_id',
        'configured_value',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'configured_value' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function salaryProfile(): BelongsTo
    {
        return $this->belongsTo(SalaryProfile::class);
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
