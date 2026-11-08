<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    public const TYPE_ALLOWANCE = 'allowance';
    public const TYPE_DEDUCTION = 'deduction';

    public const CALC_FIXED = 'fixed';
    public const CALC_PERCENTAGE = 'percentage';

    protected $fillable = [
        'name',
        'description',
        'component_type',
        'calculation_type',
        'default_value',
        'is_taxable',
        'is_active',
    ];

    protected $casts = [
        'default_value' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function profileAssignments(): HasMany
    {
        return $this->hasMany(SalaryProfileComponent::class);
    }
}
