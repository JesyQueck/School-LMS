<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeComponent extends Model
{
    protected $fillable = [
        'fee_package_id',
        'name',
        'description',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(FeePackage::class, 'fee_package_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(StudentFeeComponentSnapshot::class, 'fee_component_id');
    }
}