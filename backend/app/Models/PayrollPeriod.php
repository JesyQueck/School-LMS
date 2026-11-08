<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_PAID = 'paid';
    public const STATUS_LOCKED = 'locked';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PROCESSED,
        self::STATUS_PAID,
        self::STATUS_LOCKED,
    ];

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date',
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }
}
