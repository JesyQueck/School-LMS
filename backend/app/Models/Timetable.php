<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Timetable extends Model
{
    protected $table = 'timetables';

    protected $fillable = [
        'class_subject_id',
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'term_id',
        'academic_session_id',
        'is_locked',
        'is_manual',
        'period_config_id',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_manual' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function periodConfig(): BelongsTo
    {
        return $this->belongsTo(PeriodConfig::class);
    }

    /**
     * Validate a timetable entry for scheduling conflicts.
     *
     * @param  mixed  $excludeId  Single ID or array of IDs to exclude from conflict check
     * @return \Illuminate\Support\Collection
     */
    public static function validateTimetableEntry(Timetable $timetable, $excludeId = null): Collection
    {
        $conflicts = collect();

        $excludeIds = is_array($excludeId) ? $excludeId : ($excludeId ? [$excludeId] : []);
        if ($timetable->exists) {
            $excludeIds[] = $timetable->id;
        }
        $excludeIds = array_unique(array_filter($excludeIds));

        $startTime = $timetable->start_time instanceof Carbon
            ? $timetable->start_time->format('H:i')
            : $timetable->start_time;
        $endTime = $timetable->end_time instanceof Carbon
            ? $timetable->end_time->format('H:i')
            : $timetable->end_time;

        if ($timetable->teacher_id && $timetable->day && $startTime && $endTime) {
            $conflicting = Timetable::where('teacher_id', $timetable->teacher_id)
                ->where('day', $timetable->day);

            foreach ($excludeIds as $id) {
                $conflicting->where('id', '!=', $id);
            }

            $hasConflict = $conflicting->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($subQ) use ($startTime, $endTime) {
                        $subQ->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })->exists();

            if ($hasConflict) {
                $conflicts[] = 'Teacher is already scheduled at this time.';
            }

            $consecutiveCount = static::countConsecutiveClasses($timetable, $excludeIds);
            if ($consecutiveCount > 3) {
                $conflicts[] = 'Teacher cannot have more than 3 consecutive classes without a break.';
            }
        }

        if ($timetable->class_subject_id && $timetable->day && $startTime && $endTime) {
            $conflicting = Timetable::where('class_subject_id', $timetable->class_subject_id)
                ->where('day', $timetable->day);

            foreach ($excludeIds as $id) {
                $conflicting->where('id', '!=', $id);
            }

            $hasConflict = $conflicting->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($subQ) use ($startTime, $endTime) {
                        $subQ->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })->exists();

            if ($hasConflict) {
                $conflicts[] = 'This class already has a lesson at this time.';
            }

            $sameSubjectCount = Timetable::where('class_subject_id', $timetable->class_subject_id)
                ->where('day', $timetable->day)
                ->when($timetable->exists, function ($q) use ($excludeIds) {
                    foreach ($excludeIds as $id) {
                        $q->where('id', '!=', $id);
                    }
                })
                ->count();

            if ($sameSubjectCount >= 2) {
                $conflicts[] = 'Maximum 2 periods of the same subject per day allowed.';
            }
        }

        return $conflicts;
    }

    /**
     * Validate swapping two timetable entries.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function validateSwap(Timetable $entryA, Timetable $entryB): Collection
    {
        $conflicts = collect();

        $originalA = [
            'day' => $entryA->day,
            'start_time' => Carbon::parse($entryA->start_time)->format('H:i'),
            'end_time' => Carbon::parse($entryA->end_time)->format('H:i'),
        ];
        $originalB = [
            'day' => $entryB->day,
            'start_time' => Carbon::parse($entryB->start_time)->format('H:i'),
            'end_time' => Carbon::parse($entryB->end_time)->format('H:i'),
        ];

        $modelA = clone $entryA;
        $modelA->fill($originalB);

        $modelB = clone $entryB;
        $modelB->fill($originalA);

        $conflicts = $conflicts->merge(static::validateTimetableEntry($modelA, [$entryA->id, $entryB->id]));
        $conflicts = $conflicts->merge(static::validateTimetableEntry($modelB, [$entryA->id, $entryB->id]));

        return $conflicts->unique()->values();
    }

    /**
     * Count consecutive classes for a teacher on a given day.
     *
     * @return int
     */
    public static function countConsecutiveClasses(Timetable $timetable, array $excludeIds = []): int
    {
        $startTime = $timetable->start_time instanceof Carbon
            ? $timetable->start_time->format('H:i')
            : $timetable->start_time;
        $endTime = $timetable->end_time instanceof Carbon
            ? $timetable->end_time->format('H:i')
            : $timetable->end_time;

        if (! $timetable->teacher_id || ! $timetable->day || ! $startTime || ! $endTime) {
            return 0;
        }

        $teacherEntries = Timetable::where('teacher_id', $timetable->teacher_id)
            ->where('day', $timetable->day)
            ->where(function ($q) use ($excludeIds) {
                foreach ($excludeIds as $id) {
                    $q->where('id', '!=', $id);
                }
            })
            ->orderBy('start_time')
            ->get();

        $isAdjacent = $teacherEntries->contains(function ($entry) use ($startTime, $endTime) {
            $entryStart = Carbon::parse($entry->start_time)->format('H:i');
            $entryEnd = Carbon::parse($entry->end_time)->format('H:i');

            return $entryEnd === $startTime || $entryStart === $endTime;
        });

        if (! $isAdjacent) {
            return 0;
        }

        $consecutiveCount = 1;
        $chainEnd = $endTime;

        foreach ($teacherEntries->sortBy('start_time') as $entry) {
            $entryStart = Carbon::parse($entry->start_time)->format('H:i');
            $entryEnd = Carbon::parse($entry->end_time)->format('H:i');

            if ($entryStart === $chainEnd) {
                $consecutiveCount++;
                $chainEnd = $entryEnd;
            }
        }

        return $consecutiveCount;
    }
}
