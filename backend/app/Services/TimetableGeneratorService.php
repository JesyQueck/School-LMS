<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\PeriodConfig;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimetableGeneratorService
{
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function __construct(
        public readonly PeriodConfig $periodConfig,
    ) {}

    public function getPeriodConfig(): PeriodConfig
    {
        return $this->periodConfig;
    }

    /**
     * Get the effective end time for a given day, respecting day rules.
     */
    public function getDayEndTime(string $day): ?string
    {
        $dayRule = $this->periodConfig->getDayRule($day);
        if ($dayRule && isset($dayRule['end_override'])) {
            return $dayRule['end_override'];
        }
        return null;
    }

    /**
     * Get the event/activity override for a given period on a given day.
     */
    public function getDayPeriodOverride(string $day, int $periodNumber): ?array
    {
        $dayRule = $this->periodConfig->getDayRule($day);
        if (! $dayRule || ! isset($dayRule['period_overrides'])) {
            return null;
        }

        return $dayRule['period_overrides'][$periodNumber] ?? null;
    }

    public function generate(): Collection
    {
        $classSubjects = ClassSubject::with([
            'class',
            'subject',
            'teacherAssignments',
        ])
            ->whereHas('class')
            ->get();

        $periods = $this->periodConfig->periods;
        $teachingPeriods = $periods->where('is_break', false)->values();
        $days = $this->periodConfig->dayLabels();
        $dayCount = count($days);

        // Compute max periods per week across all tasks for loop bound
        $periodsPerWeekMax = 0;
        foreach ($classSubjects as $classSubject) {
            $pw = (int) ($classSubject->periods_per_week ?? 1);
            if ($pw < 1) {
                $pw = 1;
            }
            if ($pw > $periodsPerWeekMax) {
                $periodsPerWeekMax = $pw;
            }
        }

        $generated = collect();
        $warnings = collect();
        $conflicts = collect();

        $teacherSlots = [];
        $classSlots = [];

        // Build a list of all scheduling tasks: each class-subject needs N periods per week
        $tasks = [];
        foreach ($classSubjects as $classSubject) {
            $periodsPerWeek = (int) ($classSubject->periods_per_week ?? 1);
            if ($periodsPerWeek < 1) {
                $periodsPerWeek = 1;
            }

            $teacherAssignment = $classSubject->teacherAssignments
                ->where('is_active', true)
                ->first();

            if (! $teacherAssignment) {
                $warnings[] = [
                    'class' => $classSubject->class->name ?? 'Unknown',
                    'subject' => $classSubject->subject->name ?? 'Unknown',
                    'message' => 'No active teacher assigned for this class-subject combination.',
                ];
                continue;
            }

            for ($p = 0; $p < $periodsPerWeek; $p++) {
                $tasks[] = [
                    'class_subject_id' => $classSubject->id,
                    'class_id' => $classSubject->class_id,
                    'teacher_id' => $teacherAssignment->teacher_id,
                    'day' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'term_id' => $this->periodConfig->term_id,
                    'academic_session_id' => $this->periodConfig->academic_session_id,
                    'period_config_id' => $this->periodConfig->id,
                    'is_locked' => false,
                    'is_manual' => false,
                    'subject' => $classSubject->subject->name ?? 'N/A',
                    'class_name' => $classSubject->class->name ?? 'N/A',
                    'period_number' => null,
                    'period_name' => null,
                    'teacher_name' => $teacherAssignment->teacher->user->name ?? 'Not assigned',
                    'has_teacher' => true,
                    'day_rule' => null,
                ];
            }
        }

        // STEP 1: Reserve day-rule override slots first
        // These slots are taken by day rules and must NOT be filled by round-robin
        foreach ($days as $day) {
            $dayRule = $this->periodConfig->getDayRule($day);
            if (! $dayRule || ! isset($dayRule['period_overrides'])) {
                continue;
            }

            foreach ($dayRule['period_overrides'] as $periodNumber => $override) {
                $period = $teachingPeriods->first(fn ($p) => $p->period_number == $periodNumber);
                if (! $period) {
                    continue;
                }

                // Check day end override
                $dayEndTime = $this->getDayEndTime($day);
                if ($dayEndTime && Carbon::parse($period->end_time)->gt(Carbon::parse($dayEndTime))) {
                    continue;
                }

                $slotKey = "{$day}-{$period->period_number}";

                $periodName = $override['name'] ?? $period->name ?? 'Period ' . $period->period_number;
                $subjectName = $override['subject'] ?? $period->name ?? 'Period ' . $period->period_number;
                $teacherName = $override['teacher'] ?? 'TBD';

                // Create override entries for each class that has subjects
                foreach ($classSubjects as $classSubject) {
                    $classId = $classSubject->id;
                    if (isset($classSlots[$classId][$slotKey])) {
                        continue;
                    }

                    $teacherAssignment = $classSubject->teacherAssignments
                        ->where('is_active', true)
                        ->first();

                    $entry = [
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $teacherAssignment?->teacher_id ?? null,
                        'day' => $day,
                        'start_time' => Carbon::parse($period->start_time)->format('H:i:s'),
                        'end_time' => Carbon::parse($period->end_time)->format('H:i:s'),
                        'term_id' => $this->periodConfig->term_id,
                        'academic_session_id' => $this->periodConfig->academic_session_id,
                        'period_config_id' => $this->periodConfig->id,
                        'is_locked' => true,
                        'is_manual' => false,
                        'subject' => $subjectName,
                        'class_name' => $classSubject->class->name ?? 'N/A',
                        'period_number' => $period->period_number,
                        'period_name' => $periodName,
                        'teacher_name' => $teacherName,
                        'has_teacher' => true,
                        'day_rule' => 'day_override',
                    ];

                    $generated[] = $entry;
                    $classSlots[$classId][$slotKey] = true;
                }
            }
        }

        // STEP 2: Round-robin schedule remaining tasks into remaining slots
        $dayIndex = 0;
        $periodIndex = 0;
        $round = 0;

        while (count($tasks) > 0 && $round < $periodsPerWeekMax) {
            $round++;
            $remainingTasks = $tasks;
            $tasks = [];

            foreach ($remainingTasks as $task) {
                $assigned = false;

                // Try to find a slot for this task, scanning days and periods
                for ($d = 0; $d < $dayCount; $d++) {
                    $dayIdx = ($dayIndex + $d) % $dayCount;
                    $day = $days[$dayIdx];

                    // Check day end override - skip periods extending past day end
                    $dayEndTime = $this->getDayEndTime($day);

                    for ($p = 0; $p < count($teachingPeriods); $p++) {
                        $periodIdx = ($periodIndex + $p) % count($teachingPeriods);
                        $period = $teachingPeriods[$periodIdx];

                        // Skip periods extending past day end override
                        if ($dayEndTime && Carbon::parse($period->end_time)->gt(Carbon::parse($dayEndTime))) {
                            continue;
                        }

                        // Skip slots reserved for day-rule overrides
                        $slotKey = "{$day}-{$period->period_number}";
                        if (isset($classSlots[$task['class_id']][$slotKey])) {
                            continue;
                        }

                        $teacherId = $task['teacher_id'];
                        if ($teacherId && isset($teacherSlots[$teacherId][$slotKey])) {
                            continue;
                        }

                        $periodName = $period->name ?? 'Period ' . $period->period_number;
                        $subjectName = $task['subject'];
                        $teacherName = $task['teacher_name'];

                        $entry = [
                            'class_subject_id' => $task['class_subject_id'],
                            'teacher_id' => $teacherId,
                            'day' => $day,
                            'start_time' => Carbon::parse($period->start_time)->format('H:i:s'),
                            'end_time' => Carbon::parse($period->end_time)->format('H:i:s'),
                            'term_id' => $task['term_id'],
                            'academic_session_id' => $task['academic_session_id'],
                            'period_config_id' => $task['period_config_id'],
                            'is_locked' => false,
                            'is_manual' => false,
                            'subject' => $subjectName,
                            'class_name' => $task['class_name'],
                            'period_number' => $period->period_number,
                            'period_name' => $periodName,
                            'teacher_name' => $teacherName,
                            'has_teacher' => true,
                            'day_rule' => null,
                        ];

                        $generated[] = $entry;

                        $classSlots[$task['class_id']][$slotKey] = true;
                        if ($teacherId) {
                            $teacherSlots[$teacherId][$slotKey] = true;
                        }

                        // Advance pointers for next task
                        $dayIndex = ($dayIndex + $d + 1) % $dayCount;
                        $periodIndex = ($periodIndex + $p + 1) % count($teachingPeriods);

                        $assigned = true;
                        break;
                    }

                    if ($assigned) {
                        break;
                    }
                }

                if (! $assigned) {
                    // Could not schedule this task, keep it for next round or warn
                    $tasks[] = $task;
                }
            }

            // Prevent infinite loop if no progress
            if (count($tasks) === count($remainingTasks)) {
                break;
            }
        }

        // Warn about unassigned tasks
        foreach ($tasks as $task) {
            $warnings[] = [
                'class' => $task['class_name'],
                'subject' => $task['subject'],
                'message' => 'Could not schedule all periods for this class-subject combination.',
            ];
        }

        return collect([
            'entries' => $generated,
            'warnings' => $warnings,
            'conflicts' => $conflicts,
        ]);
    }

    public function saveGenerated(array $entries): void
    {
        Timetable::where('period_config_id', $this->periodConfig->id)
            ->where('term_id', $this->periodConfig->term_id)
            ->where('is_manual', false)
            ->where('is_locked', false)
            ->delete();

        foreach ($entries as $entry) {
            Timetable::create([
                'class_subject_id' => $entry['class_subject_id'],
                'teacher_id' => $entry['teacher_id'],
                'day' => $entry['day'],
                'start_time' => Carbon::parse($entry['start_time'])->format('H:i:s'),
                'end_time' => Carbon::parse($entry['end_time'])->format('H:i:s'),
                'term_id' => $entry['term_id'],
                'academic_session_id' => $entry['academic_session_id'],
                'period_config_id' => $entry['period_config_id'],
                'period_number' => $entry['period_number'] ?? null,
                'period_name_override' => $entry['period_name'] ?? null,
                'is_locked' => $entry['is_locked'] ?? false,
                'is_manual' => $entry['is_manual'] ?? false,
            ]);
        }
    }

    public function validatePeriods(array $periods): Collection
    {
        $errors = collect();
        $previousEnd = null;
        $teachingPeriodCount = 0;

        foreach ($periods as $index => $period) {
            $start = $period['start_time'] ?? null;
            $end = $period['end_time'] ?? null;
            $isBreak = $period['is_break'] ?? false;

            if (! $start || ! $end) {
                continue;
            }

            if ($start >= $end) {
                $errors[] = 'Period '.($period['period_number'] ?? $index + 1).': start time must be before end time.';
            }

            if ($teachingPeriodCount > 0 && ! $isBreak && $previousEnd) {
                if (Carbon::parse($start)->lt(Carbon::parse($previousEnd))) {
                    $errors[] = 'Period '.($period['period_number'] ?? $index + 1).': start time overlaps with previous period.';
                }
            }

            if ($start < $previousEnd && $previousEnd && ! $isBreak) {
                $errors[] = 'Period '.($period['period_number'] ?? $index + 1).': overlaps with previous period.';
            }

            if (! $isBreak) {
                $previousEnd = $end;
                $teachingPeriodCount++;
            }
        }

        $periodNumbers = array_column($periods, 'period_number');
        $uniqueNumbers = array_unique($periodNumbers);

        if (count($periodNumbers) !== count($uniqueNumbers)) {
            $errors[] = 'Duplicate period numbers detected.';
        }

        return $errors;
    }

    public function validateTimetableEntry(Timetable $timetable, $excludeId = null): Collection
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

            $consecutiveCount = $this->countConsecutiveClasses($timetable, $excludeIds);
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

    public function countConsecutiveClasses(Timetable $timetable, array $excludeIds = []): int
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

    public function validateSwap(Timetable $entryA, Timetable $entryB): Collection
    {
        $conflicts = collect();

        $originalA = ['day' => $entryA->day, 'start_time' => Carbon::parse($entryA->start_time)->format('H:i'), 'end_time' => Carbon::parse($entryA->end_time)->format('H:i')];
        $originalB = ['day' => $entryB->day, 'start_time' => Carbon::parse($entryB->start_time)->format('H:i'), 'end_time' => Carbon::parse($entryB->end_time)->format('H:i')];

        $modelA = clone $entryA;
        $modelA->fill($originalB);

        $modelB = clone $entryB;
        $modelB->fill($originalA);

        $conflicts = $conflicts->merge($this->validateTimetableEntry($modelA, [$entryA->id, $entryB->id]));
        $conflicts = $conflicts->merge($this->validateTimetableEntry($modelB, [$entryA->id, $entryB->id]));

        return $conflicts->unique()->values();
    }
}
