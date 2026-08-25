<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'term_id',
        'class_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }
}
