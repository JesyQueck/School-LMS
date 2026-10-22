<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminDashboardAnalytics
{
    public function attendance(Term $term, Collection $classes, ?CarbonImmutable $date, ?int $selectedClassId): array
    {
        $trendRows = Attendance::query()
            ->where('term_id', $term->id)
            ->select(['date', 'status'])
            ->selectRaw('COUNT(*) as total')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        $trendLabels = $trendRows->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->format('M j, Y'))
            ->unique()
            ->values();
        $statuses = $this->orderedStatuses($trendRows->pluck('status'));
        $trendDatasets = $statuses->map(function (string $status) use ($trendRows, $trendLabels) {
            $countsByDate = $trendRows
                ->filter(fn ($row) => $this->normalizeStatus($row->status) === $status)
                ->mapWithKeys(fn ($row) => [
                    CarbonImmutable::parse($row->date)->format('M j, Y') => (int) $row->total,
                ]);

            return [
                'label' => $this->statusLabel($status),
                'data' => $trendLabels->map(fn ($label) => $countsByDate->get($label, 0))->all(),
            ];
        })->values();

        $classRows = collect();
        if ($date) {
            $classRows = Attendance::query()
                ->join('classes', 'attendances.class_id', '=', 'classes.id')
                ->where('attendances.term_id', $term->id)
                ->whereDate('attendances.date', $date->toDateString())
                ->select(['attendances.class_id', 'classes.name', 'attendances.status'])
                ->selectRaw('COUNT(*) as total')
                ->groupBy('attendances.class_id', 'classes.name', 'attendances.status')
                ->get();
        }

        $classStatuses = $this->orderedStatuses($classRows->pluck('status'));
        $classLabels = $classes->pluck('name')->values();
        $classDatasets = $classStatuses->map(function (string $status) use ($classRows, $classes) {
            $countsByClass = $classRows
                ->filter(fn ($row) => $this->normalizeStatus($row->status) === $status)
                ->mapWithKeys(fn ($row) => [(int) $row->class_id => (int) $row->total]);

            return [
                'label' => $this->statusLabel($status),
                'data' => $classes->map(fn (SchoolClass $class) => $countsByClass->get($class->id, 0))->all(),
            ];
        })->values();
        $comparisonRows = collect($this->tableRows($classLabels, $classDatasets))->map(function (array $row, int $index) use ($classDatasets) {
            $row['records'] = collect($classDatasets)->sum(fn ($dataset) => (int) ($dataset['data'][$index] ?? 0));
            $row['hasRecords'] = $row['records'] > 0;

            return $row;
        });

        $classDetailRows = collect();
        $detailRosterCount = 0;
        $detailCoveredCount = 0;
        $detailCoverage = null;
        $detailCanShowCoverage = $date?->isToday() ?? false;

        if ($date && $selectedClassId) {
            $classDetailStatusRows = Attendance::query()
                ->where('term_id', $term->id)
                ->where('class_id', $selectedClassId)
                ->whereDate('date', $date->toDateString())
                ->select('status')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('status')
                ->get();
            $classDetailRows = $this->orderedStatuses($classDetailStatusRows->pluck('status'))
                ->map(function (string $status) use ($classDetailStatusRows) {
                    $statusRow = $classDetailStatusRows->first(
                        fn ($row) => $this->normalizeStatus($row->status) === $status
                    );

                    return [
                        'label' => $this->statusLabel($status),
                        'count' => (int) $statusRow->total,
                    ];
                });

            if ($detailCanShowCoverage) {
                // Completion counts eligible active students with any matching register row, regardless of status.
                $detailRosterCount = $this->activeRosterQuery($selectedClassId)->count();
                $detailCoveredCount = Attendance::query()
                    ->join('students', function ($join) use ($selectedClassId) {
                        $join->on('attendances.student_id', '=', 'students.id')
                            ->where('students.class_id', '=', $selectedClassId)
                            ->whereColumn('attendances.class_id', 'students.class_id');
                    })
                    ->join('users', function ($join) {
                        $join->on('students.user_id', '=', 'users.id')
                            ->where('users.is_active', true);
                    })
                    ->where('attendances.term_id', $term->id)
                    ->where('attendances.class_id', $selectedClassId)
                    ->whereDate('attendances.date', $date->toDateString())
                    ->where('students.status', 'active')
                    ->distinct()
                    ->count('students.id');

                if ($detailRosterCount > 0 && $detailCoveredCount > 0) {
                    $detailCoverage = round(($detailCoveredCount / $detailRosterCount) * 100, 1);
                }
            }
        }

        $today = CarbonImmutable::today();
        $todayInTerm = (! $term->start_date || $today->greaterThanOrEqualTo($term->start_date->startOfDay()))
            && (! $term->end_date || $today->lessThanOrEqualTo($term->end_date->endOfDay()));
        $todayRows = collect();
        $todayRosterCount = $this->activeRosterQuery()->count();
        $todayCoveredCount = 0;
        $todayCoverage = null;

        if ($todayInTerm) {
            $todayRows = $this->activeRosterAttendanceQuery($term, $today)
                ->select('attendances.status')
                ->selectRaw('COUNT(DISTINCT students.id) as total')
                ->groupBy('attendances.status')
                ->get()
                ->map(fn ($row) => [
                    'label' => $this->statusLabel($this->normalizeStatus($row->status)),
                    'count' => (int) $row->total,
                ]);

            $todayCoveredCount = (int) $this->activeRosterAttendanceQuery($term, $today)
                ->distinct()
                ->count('students.id');

            // Coverage is distinct matching active students divided by the active roster, not present / roster.
            if ($todayRosterCount > 0 && $todayCoveredCount > 0) {
                $todayCoverage = round(($todayCoveredCount / $todayRosterCount) * 100, 1);
            }
        }

        return [
            'syntheticCount' => Schema::hasColumn('attendances', 'is_synthetic')
                ? Attendance::query()->where('term_id', $term->id)->where('is_synthetic', true)->count()
                : 0,
            'trend' => [
                'labels' => $trendLabels->all(),
                'datasets' => $trendDatasets->all(),
                'rows' => $this->tableRows($trendLabels, $trendDatasets),
            ],
            'classComparison' => [
                'labels' => $classLabels->all(),
                'datasets' => $classDatasets->all(),
                'rows' => $comparisonRows->all(),
                'hasRecords' => $classRows->isNotEmpty(),
            ],
            'classDetail' => [
                'rows' => $classDetailRows->all(),
                'hasRecords' => $classDetailRows->isNotEmpty(),
                'roster' => $detailRosterCount,
                'covered' => $detailCoveredCount,
                'coverage' => $detailCoverage,
                'coverageAvailable' => $detailCoverage !== null,
            ],
            'today' => [
                'rows' => $todayRows->all(),
                'hasRecords' => $todayRows->isNotEmpty(),
                'roster' => $todayRosterCount,
                'covered' => $todayCoveredCount,
                'coverage' => $todayCoverage,
                'available' => $todayInTerm,
            ],
            'enrollment' => $this->enrollmentByClass($classes),
        ];
    }

    public function activeStudentCount(): int
    {
        return $this->activeRosterQuery()->count();
    }

    public function enrollment(Collection $classes): array
    {
        return $this->enrollmentByClass($classes);
    }

    private function activeRosterQuery(?int $classId = null)
    {
        return Student::query()
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->when($classId, fn ($query) => $query->where('class_id', $classId));
    }

    private function activeRosterAttendanceQuery(Term $term, CarbonImmutable $date)
    {
        return Attendance::query()
            ->join('students', function ($join) {
                $join->on('attendances.student_id', '=', 'students.id')
                    ->whereColumn('attendances.class_id', 'students.class_id');
            })
            ->join('users', function ($join) {
                $join->on('students.user_id', '=', 'users.id')
                    ->where('users.is_active', true);
            })
            ->where('attendances.term_id', $term->id)
            ->whereDate('attendances.date', $date->toDateString())
            ->where('students.status', 'active');
    }

    private function enrollmentByClass(Collection $classes): array
    {
        $counts = Student::query()
            ->where('students.status', 'active')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->select('class_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        return $classes->map(fn (SchoolClass $class) => [
            'label' => $class->name,
            'count' => (int) $counts->get($class->id, 0),
        ])->all();
    }

    private function orderedStatuses(Collection $statuses): Collection
    {
        return $statuses
            ->map(fn ($status) => $this->normalizeStatus($status))
            ->unique()
            ->sortBy(fn (string $status) => match ($status) {
                'present' => 0,
                'absent' => 1,
                'late' => 2,
                default => 3,
            })
            ->values();
    }

    private function normalizeStatus(mixed $status): string
    {
        return strtolower(trim((string) $status)) ?: 'other';
    }

    private function statusLabel(string $status): string
    {
        return ucfirst($status);
    }

    private function tableRows(Collection $labels, Collection $datasets): array
    {
        return $labels->values()->map(function (string $label, int $index) use ($datasets) {
            $row = ['label' => $label];

            foreach ($datasets as $dataset) {
                $row[$dataset['label']] = (int) ($dataset['data'][$index] ?? 0);
            }

            return $row;
        })->all();
    }
}
