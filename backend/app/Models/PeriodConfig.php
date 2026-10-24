<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodConfig extends Model
{
    protected $fillable = [
        'academic_session_id',
        'term_id',
        'periods_per_day',
        'start_day',
        'end_day',
        'day_rules',
    ];

    protected $casts = [
        'day_rules' => 'array',
    ];

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class)->orderBy('sort_order');
    }

    public function dayLabels(): array
    {
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $start = array_search($this->start_day, $allDays);
        $end = array_search($this->end_day, $allDays);

        if ($start === false || $end === false) {
            $start = 0;
            $end = 4;
        }

        return array_slice($allDays, $start, $end - $start + 1);
    }

    /**
     * Get day-specific rules for a given day.
     * Returns null if no rules defined for that day.
     */
    public function getDayRule(string $day): ?array
    {
        if (! is_array($this->day_rules)) {
            return null;
        }

        return $this->day_rules[$day] ?? null;
    }

    /**
     * Get all day rules.
     */
    public function getDayRules(): array
    {
        return is_array($this->day_rules) ? $this->day_rules : [];
    }
}
