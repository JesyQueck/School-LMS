<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    public const EMPLOYMENT_TYPE_FULL_TIME = 'full-time';
    public const EMPLOYMENT_TYPE_PART_TIME = 'part-time';
    public const EMPLOYMENT_TYPE_CONTRACT = 'contract';

    public const EMPLOYMENT_TYPES = [
        self::EMPLOYMENT_TYPE_FULL_TIME,
        self::EMPLOYMENT_TYPE_PART_TIME,
        self::EMPLOYMENT_TYPE_CONTRACT,
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_LEAVE = 'on_leave';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_RETIRED = 'retired';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_ON_LEAVE,
        self::STATUS_TERMINATED,
        self::STATUS_RETIRED,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'employee_number',
        'employment_type',
        'date_employed',
        'date_left',
        'department',
        'designation',
        'employment_status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'tax_id',
        'pension_id',
    ];

    protected $casts = [
        'date_employed' => 'date',
        'date_left' => 'date',
        'bank_account_number' => 'encrypted',
        'bank_account_name' => 'encrypted',
        'tax_id' => 'encrypted',
        'pension_id' => 'encrypted',
    ];

    protected $hidden = [
        'bank_account_number',
        'bank_account_name',
        'tax_id',
        'pension_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryProfiles(): HasMany
    {
        return $this->hasMany(SalaryProfile::class);
    }

    public function currentSalaryProfile(): ?SalaryProfile
    {
        return $this->salaryProfiles()->where('is_active', true)->where('effective_from', '<=', now())->where(function ($q) {
            $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
        })->first();
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }
}
