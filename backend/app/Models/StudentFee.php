<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudentFee extends Model
{
    protected $fillable = [
        'student_id',
        'fee_type_id',
        'fee_source_type',
        'fee_package_id',
        'term_id',
        'amount_expected',
        'status',
    ];

    protected $casts = [
        'amount_expected' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    public function feePackage(): BelongsTo
    {
        return $this->belongsTo(FeePackage::class, 'fee_package_id');
    }

    public function feeSource(): MorphTo
    {
        return $this->morphTo('fee_source', 'fee_source_type', 'fee_type_id')
            ->withDefault([
                'name' => 'Unknown Fee',
            ]);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function componentSnapshots(): HasMany
    {
        return $this->hasMany(StudentFeeComponentSnapshot::class, 'student_fee_id');
    }

    public function getFeeNameAttribute(): string
    {
        if ($this->fee_source_type === 'fee_package' && $this->feePackage) {
            return $this->feePackage->name;
        }
        if ($this->feeType) {
            return $this->feeType->name;
        }
        if ($this->feeSource && $this->feeSource->exists) {
            return $this->feeSource->name;
        }
        return 'Unknown Fee';
    }

    public function getFeeComponentsAttribute()
    {
        if ($this->fee_source_type === 'fee_package' && $this->feePackage) {
            return $this->feePackage->components;
        }
        return collect();
    }
}