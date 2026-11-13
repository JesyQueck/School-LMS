<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::saving(function (PayrollPeriod $period): void {
            $startDate = $period->start_date;
            $endDate = $period->end_date;

            if (! $startDate || ! $endDate) {
                return;
            }

            if ($startDate->gt($endDate)) {
                throw ValidationException::withMessages([
                    'end_date' => 'The payroll period end date must be on or after its start date.',
                ]);
            }

            $overlapQuery = static::query()
                ->whereDate('start_date', '<=', $endDate->toDateString())
                ->whereDate('end_date', '>=', $startDate->toDateString());

            if ($period->exists) {
                $overlapQuery->where('id', '!=', $period->getKey());
            }

            if ($overlapQuery->exists()) {
                throw ValidationException::withMessages([
                    'start_date' => 'This payroll period overlaps an existing payroll period.',
                ]);
            }
        });
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }
}
