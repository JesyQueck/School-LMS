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
    ];

    protected $casts = [
        'is_break' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function periodConfig(): BelongsTo
    {
        return $this->belongsTo(PeriodConfig::class);
    }
}
