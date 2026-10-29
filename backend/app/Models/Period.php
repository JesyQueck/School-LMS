<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Period extends Model
{
    protected $fillable = [
        'period_config_id',
        'period_number',
        'name',
        'start_time',
        'end_time',
        'is_break',
        'sort_order',
        'type',
    ];

    protected $casts = [
        'is_break' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public const TYPE_TEACHING = 'teaching';
    public const TYPE_BREAK = 'break';
    public const TYPE_ACTIVITY = 'activity';

    public function periodConfig(): BelongsTo
    {
        return $this->belongsTo(PeriodConfig::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function isTeaching(): bool
    {
        return $this->type === self::TYPE_TEACHING;
    }

    public function isBreak(): bool
    {
        return $this->is_break || $this->type === self::TYPE_BREAK;
    }

    public function isActivity(): bool
    {
        return $this->type === self::TYPE_ACTIVITY;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_BREAK => 'Break',
            self::TYPE_ACTIVITY => 'Activity',
            default => 'Teaching',
        };
    }
}
