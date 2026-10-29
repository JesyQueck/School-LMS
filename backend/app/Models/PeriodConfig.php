<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodConfig extends Model
{
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    public const ALL_DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    protected $fillable = [
        'academic_session_id',
        'term_id',
        'periods_per_day',
        'start_day',
        'end_day',
        'schedule_name',
        'day_of_week',
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

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function dayLabels(): array
    {
        $dayRules = $this->day_rules;
        if (is_string($dayRules)) {
            try {
                $decoded = json_decode($dayRules, true);
                $dayRules = is_array($decoded) ? $decoded : null;
            } catch (\Throwable $e) {
                $dayRules = null;
            }
        }

        if ($dayRules && is_array($dayRules)) {
            $days = array_values(array_filter($dayRules, fn ($v) => is_string($v)));
            if (!empty($days)) {
                return array_values(array_intersect(self::ALL_DAYS, $days)) ?: self::ALL_DAYS;
            }
        }

        $allDays = self::ALL_DAYS;
        $start = array_search($this->start_day, $allDays);
        $end = array_search($this->end_day, $allDays);

        if ($start === false || $end === false) {
            $start = 0;
            $end = 4;
        }

        return array_slice($allDays, $start, $end - $start + 1);
    }

    public function hasSchedule(string $day): bool
    {
        if ($this->day_rules && is_array($this->day_rules)) {
            return in_array($day, $this->day_rules);
        }

        if ($this->day_of_week === null) {
            return true;
        }

        return $this->day_of_week === $day;
    }

    public function belongsToDay(string $day): bool
    {
        if ($this->day_rules && is_array($this->day_rules)) {
            return in_array($day, $this->day_rules);
        }
        return $this->day_of_week === null || $this->day_of_week === $day;
    }
}
