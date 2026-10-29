<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\PeriodConfig;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Timetable;
use App\Traits\AuditsActions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    use AuditsActions;

    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private function resolveCurrentTerm(): ?Term
    {
        return Term::where('is_current', true)
            ->with('academicSession')
            ->first();
    }

    private function calculatePeriodTimes(?PeriodConfig $config): array
    {
        $times = [];

        if (! $config) {
            return $times;
        }

        $periods = $config->periods()->orderBy('sort_order')->get();

        foreach ($periods as $period) {
            $periodKey = 'period_' . $period->period_number;

            $times[$periodKey] = [
                'start' => $period->start_time,
                'end' => $period->end_time,
                'name' => $period->name,
                'is_break' => $period->is_break,
                'period_number' => $period->period_number,
                'type' => $period->type ?? 'teaching',
            ];
        }

        return $times;
    }

    private function calculatePeriodTimesForDay(?PeriodConfig $config, string $day): array
    {
        if (! $config) {
            return [];
        }

        $dayConfig = $this->resolveConfigForDay($config, $day);

        return $this->calculatePeriodTimes($dayConfig);
    }

    private function getActivePeriodsForDay($configs, string $day): \Illuminate\Support\Collection
    {
        foreach ($configs as $cfg) {
            if ($cfg->day_of_week === null || $cfg->day_of_week === $day) {
                $dayConfig = $cfg;
                break;
            }
            if ($cfg->day_rules && is_array($cfg->day_rules) && in_array($day, $cfg->day_rules)) {
                $dayConfig = $cfg;
                break;
            }
        }

        if (! isset($dayConfig)) {
            $dayConfig = $configs->first();
        }

        return $dayConfig?->periods ?? collect();
    }

    private function detectConflicts($timetable): array
    {
        $conflicts = [];
        $seen = [];

        foreach ($timetable as $entry) {
            $classId = optional($entry->classSubject)->class_id ?? 0;
            $key = $day = $entry->day . '|' . $classId . '|' . \Carbon\Carbon::parse($entry->start_time)->format('H:i');

            if (isset($seen[$key])) {
                $existing = $seen[$key];
                $conflicts[] = [
                    'day' => $entry->day,
                    'class' => optional($entry->classSubject)->class->name ?? 'Unknown',
                    'period' => $entry->classSubject->subject->name ?? 'Unknown',
                    'existing_period' => $existing->classSubject->subject->name ?? 'Unknown',
                    'time' => \Carbon\Carbon::parse($entry->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($entry->end_time)->format('g:i A'),
                ];
            } else {
                $seen[$key] = $entry;
            }
        }

        return $conflicts;
    }

    private function resolveConfig(?Term $currentTerm): ?PeriodConfig
    {
        if (! $currentTerm) {
            return null;
        }

        $config = PeriodConfig::where('term_id', $currentTerm->id)
            ->where(function ($q) {
                $q->whereNull('day_of_week')
                    ->orWhere('day_of_week', 'Monday');
            })
            ->with('periods')
            ->first();

        if (! $config) {
            $configs = PeriodConfig::where('term_id', $currentTerm->id)
                ->whereNotNull('day_rules')
                ->with('periods')
                ->get();

            foreach ($configs as $c) {
                $dayRules = $c->day_rules;
                if (is_string($dayRules)) {
                    $dayRules = json_decode($dayRules, true);
                }
                if (is_array($dayRules) && in_array('Monday', $dayRules)) {
                    $config = $c;
                    break;
                }
            }
        }

        if (! $config) {
            $config = PeriodConfig::where('term_id', $currentTerm->id)
                ->with('periods')
                ->first();
        }

        if (! $config) {
            $config = PeriodConfig::create([
                'academic_session_id' => $currentTerm->academic_session_id,
                'term_id' => $currentTerm->id,
                'periods_per_day' => 8,
                'start_day' => 'Monday',
                'end_day' => 'Friday',
                'schedule_name' => 'Standard Day',
            ]);
            $this->ensurePeriods($config);
            $config->load('periods');
        }

        return $config;
    }

    private function getAllConfigs(?Term $currentTerm): \Illuminate\Support\Collection
    {
        if (! $currentTerm) {
            return collect();
        }

        return PeriodConfig::where('term_id', $currentTerm->id)
            ->with('periods')
            ->get();
    }

    private function resolveConfigForDay(PeriodConfig $config, string $day): ?PeriodConfig
    {
        if ($config->day_of_week === null || $config->day_of_week === $day) {
            return $config;
        }

        if ($config->day_rules && is_array($config->day_rules) && in_array($day, $config->day_rules)) {
            return $config;
        }

        $dayConfig = PeriodConfig::where('term_id', $config->term_id)
            ->where(function ($q) use ($day) {
                $q->where('day_of_week', $day)
                    ->orWhereNull('day_of_week');
            })
            ->with('periods')
            ->first();

        return $dayConfig ?? $config;
    }

    private function ensurePeriods(PeriodConfig $config): void
    {
        if ($config->periods()->count() > 0) {
            return;
        }

        $defaultPeriods = [
            ['period_number' => 1, 'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '08:40', 'is_break' => false],
            ['period_number' => 2, 'name' => 'Period 2', 'start_time' => '08:40', 'end_time' => '09:20', 'is_break' => false],
            ['period_number' => 3, 'name' => 'Period 3', 'start_time' => '09:20', 'end_time' => '10:00', 'is_break' => false],
            ['period_number' => 4, 'name' => 'Break', 'start_time' => '10:00', 'end_time' => '10:10', 'is_break' => true],
            ['period_number' => 5, 'name' => 'Period 4', 'start_time' => '10:10', 'end_time' => '10:50', 'is_break' => false],
            ['period_number' => 6, 'name' => 'Period 5', 'start_time' => '10:50', 'end_time' => '11:30', 'is_break' => false],
            ['period_number' => 7, 'name' => 'Break', 'start_time' => '11:30', 'end_time' => '12:00', 'is_break' => true],
            ['period_number' => 8, 'name' => 'Period 6', 'start_time' => '12:00', 'end_time' => '12:40', 'is_break' => false],
            ['period_number' => 9, 'name' => 'Period 7', 'start_time' => '12:40', 'end_time' => '13:20', 'is_break' => false],
            ['period_number' => 10, 'name' => 'Period 8', 'start_time' => '13:20', 'end_time' => '14:00', 'is_break' => false],
        ];

        $slice = $config->periods_per_day + 1;
        $toInsert = array_slice($defaultPeriods, 0, $slice);

        foreach ($toInsert as $index => $p) {
            $config->periods()->create([
                'period_number' => $p['period_number'],
                'name' => $p['name'],
                'start_time' => $p['start_time'],
                'end_time' => $p['end_time'],
                'is_break' => $p['is_break'],
                'type' => $p['is_break'] ? 'break' : 'teaching',
                'sort_order' => $index,
            ]);
        }
    }

    private function loadData($config, $currentTerm): array
    {
        $classes = SchoolClass::with('classSubjects.subject')
            ->get()
            ->sortBy(function ($cls) {
                $name = $cls->name;
                $level = 0;
                if (preg_match('/JSS\s*(\d)/i', $name, $m)) {
                    $level = 1000 + (int) $m[1];
                } elseif (preg_match('/SSS\s*(\d)/i', $name, $m)) {
                    $level = 2000 + (int) $m[1];
                } elseif (preg_match('/JS\s*(\d)/i', $name, $m)) {
                    $level = 1000 + (int) $m[1];
                } elseif (preg_match('/SS\s*(\d)/i', $name, $m)) {
                    $level = 2000 + (int) $m[1];
                }
                return $level;
            })->values();

        $teachers = Teacher::with('user')->get();
        $terms = Term::whereHas('academicSession', function ($q) {
            $q->where('is_current', true);
        })->get();
        $sessions = AcademicSession::where('is_current', true)->get();
        $subjects = Subject::all();
        $periodConfigs = PeriodConfig::with('periods')->get();

        $classSubjects = ClassSubject::with(['class', 'subject', 'teacherAssignments.teacher.user'])->get();

        $configs = null;
        if ($currentTerm) {
            $configs = PeriodConfig::where('term_id', $currentTerm->id)
                ->with('periods')
                ->get();
        }

        return compact(
            'classes',
            'teachers',
            'terms',
            'sessions',
            'subjects',
            'currentTerm',
            'config',
            'configs',
            'periodConfigs',
            'classSubjects'
        );
    }

    public function index(Request $request)
    {
        $currentTerm = $this->resolveCurrentTerm();
        $config = $this->resolveConfig($currentTerm);

        if ($config && $config->periods()->count() === 0) {
            $this->ensurePeriods($config);
            $config->load('periods');
        }

        $classId = $request->query('class_id');
        $teacherId = $request->query('teacher_id');
        $subjectId = $request->query('subject_id');
        $day = $request->query('day');
        $sessionId = $request->query('session_id');
        $termId = $request->query('term_id');

        $query = Timetable::with([
            'classSubject.class',
            'classSubject.subject',
            'teacher.user',
            'term',
            'academicSession',
        ])->orderByRaw(
            "CASE day WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 ELSE 6 END"
        )->orderBy('start_time');

        $queryId = $termId ?: $currentTerm?->id;
        if ($queryId) {
            $query->where('term_id', $queryId);
        }

        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        if ($classId) {
            $query->whereHas('classSubject.class', function ($q) use ($classId) {
                $q->where('id', $classId);
            });
        }

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        if ($subjectId) {
            $query->whereHas('classSubject.subject', function ($q) use ($subjectId) {
                $q->where('id', $subjectId);
            });
        }

        if ($day) {
            $query->where('day', $day);
        }

        $timetable = $query->get();

        $configs = $this->getAllConfigs($currentTerm);

        $periodTimes = $this->calculatePeriodTimes($config);

        $periodTimesByDay = [];
        foreach (self::DAYS as $day) {
            $periodTimesByDay[$day] = $this->calculatePeriodTimesForDay($config, $day);
        }

        $gridData = [];
        foreach ($timetable as $entry) {
            $classIdKey = optional($entry->classSubject)->class_id ?? 0;
            $periodStart = Carbon::parse($entry->start_time)->format('H:i');
            $gridData[$entry->day][$classIdKey][$periodStart] = $entry;
        }

        $periodsByDay = [];
        foreach (self::DAYS as $day) {
            $dayConfig = null;
            if ($configs) {
                foreach ($configs as $c) {
                    $dayRules = $c->day_rules;
                    if (is_string($dayRules)) {
                        $dayRules = json_decode($dayRules, true);
                        $dayRules = is_array($dayRules) ? $dayRules : null;
                    }
                    if ($c->day_of_week === null || $c->day_of_week === $day || (is_array($dayRules) && in_array($day, $dayRules))) {
                        $dayConfig = $c;
                        break;
                    }
                }
            }
            if (! $dayConfig) {
                $dayConfig = $config;
            }
            $periodsByDay[$day] = $dayConfig ? $dayConfig->periods->where('is_break', false)->sortBy('sort_order') : collect();
        }

        $conflicts = $this->detectConflicts($timetable);

        $data = $this->loadData($config, $currentTerm);
        $data['timetable'] = $timetable;
        $data['gridData'] = $gridData;
        $data['periodTimes'] = $periodTimes;
        $data['periodTimesByDay'] = $periodTimesByDay;
        $data['periodsByDay'] = $periodsByDay;
        $data['conflicts'] = $conflicts;
        $data['configs'] = $configs;
        $data['request'] = $request;

        return view('admin.timetable.index', $data);
    }

    public function config(Request $request)
    {
        $currentTerm = $this->resolveCurrentTerm();
        $configs = $this->getAllConfigs($currentTerm);

        $config = null;
        if ($configs->isNotEmpty()) {
            $config = $configs->first(fn ($c) => $c->day_of_week === null);

            if (! $config) {
                $config = $configs->firstWhere('day_of_week', 'Monday');
            }

            if (! $config) {
                foreach ($configs as $c) {
                    if ($c->belongsToDay('Monday')) {
                        $config = $c;
                        break;
                    }
                }
            }

            if (! $config) {
                $config = $configs->first();
            }
        }

        if (! $config && $currentTerm) {
            $config = PeriodConfig::create([
                'academic_session_id' => $currentTerm?->academic_session_id,
                'term_id' => $currentTerm?->id,
                'periods_per_day' => 8,
                'start_day' => 'Monday',
                'end_day' => 'Friday',
                'schedule_name' => 'Standard Day',
            ]);
            $this->ensurePeriods($config);
            $config->load('periods');
            $configs = $this->getAllConfigs($currentTerm);
        }

        if ($config && $config->periods()->count() === 0) {
            $this->ensurePeriods($config);
            $config->load('periods');
        }

        $sessions = AcademicSession::all();
        $terms = Term::with('academicSession')->get();

        return view('admin.timetable.config', compact(
            'config',
            'configs',
            'currentTerm',
            'sessions',
            'terms'
        ));
    }

    public function saveConfig(Request $request)
    {
        $currentTerm = $this->resolveCurrentTerm();

        $validated = $request->validate([
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'schedule_name' => ['nullable', 'string', 'max:255'],
            'periods_per_day' => ['nullable', 'integer', 'min:1', 'max:20'],
            'start_day' => ['nullable', 'in:'.implode(',', self::DAYS)],
            'end_day' => ['nullable', 'in:'.implode(',', self::DAYS)],
            'active_days' => ['nullable', 'array'],
            'active_days.*' => ['in:'.implode(',', self::DAYS)],
            'schedules' => ['nullable', 'array'],
            'schedules.*.name' => ['required', 'string', 'max:255'],
            'schedules.*.day_of_week' => ['nullable', 'string', 'max:255'],
            'schedules.*.days' => ['nullable', 'array'],
            'schedules.*.days.*' => ['in:'.implode(',', self::DAYS)],
            'schedules.*.select_all_days' => ['nullable', 'boolean'],
            'schedules.*.periods' => ['required', 'array', 'min:1'],
            'schedules.*.periods.*.period_number' => ['required', 'integer', 'min:1'],
            'schedules.*.periods.*.name' => ['required', 'string', 'max:255'],
            'schedules.*.periods.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.periods.*.end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'schedules.*.periods.*.is_break' => ['nullable', 'boolean'],
            'schedules.*.periods.*.type' => ['nullable', 'in:teaching,break,activity'],
            'schedules.*.periods.*.sort_order' => ['required', 'integer', 'min:0'],
            'periods' => ['nullable', 'array'],
            'periods.*.period_number' => ['required', 'integer', 'min:1'],
            'periods.*.name' => ['required', 'string', 'max:255'],
            'periods.*.start_time' => ['required', 'date_format:H:i'],
            'periods.*.end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'periods.*.is_break' => ['nullable', 'boolean'],
            'periods.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($currentTerm, $validated, $request) {
            $termId = $validated['term_id'] ?? $currentTerm?->id;
            $sessionId = $validated['academic_session_id'] ?? $currentTerm?->academic_session_id;

            if (isset($validated['schedules']) && count($validated['schedules']) > 0) {
                $this->saveSchedules($termId, $sessionId, $validated['schedules']);
            } else {
                $this->saveSingleConfig($termId, $sessionId, $validated);
            }

            $this->audit($request, 'timetable.config.saved', PeriodConfig::class, null, null, $validated);
        });

        return redirect()->route('admin.timetable.index')->with('status', 'Timetable configuration saved successfully.');
    }

    private function saveSchedules(int $termId, int $sessionId, array $schedules): void
    {
        $existingConfigIds = [];

        foreach ($schedules as $scheduleData) {
            $selectAlldays = $scheduleData['select_all_days'] ?? false;
            $days = $scheduleData['days'] ?? null;

            $days = is_array($days)
                ? array_values(array_filter($days, fn ($v) => is_string($v) && in_array($v, self::DAYS)))
                : $days;

            if ($selectAlldays) {
                $days = [];
            }

            if (is_array($days) && count($days) > 0) {
                $dayOfWeek = null;
                $dayRules = $days;
            } elseif (isset($scheduleData['day_of_week']) && $scheduleData['day_of_week'] !== 'all' && !empty($scheduleData['day_of_week'])) {
                $dayOfWeek = $scheduleData['day_of_week'];
                $dayRules = null;
            } else {
                $dayOfWeek = null;
                $dayRules = null;
            }

            $configData = [
                'academic_session_id' => $sessionId,
                'term_id' => $termId,
                'schedule_name' => $scheduleData['name'],
                'day_of_week' => $dayOfWeek,
                'periods_per_day' => count($scheduleData['periods']),
                'start_day' => $dayOfWeek ?? (is_array($days) && count($days) > 0 ? $days[0] : 'Monday'),
                'end_day' => $dayOfWeek ?? (is_array($days) && count($days) > 0 ? $days[0] : 'Friday'),
            ];

            if ($dayRules !== null) {
                $configData['day_rules'] = $dayRules;
            }

            $config = PeriodConfig::firstOrNew([
                'term_id' => $termId,
                'schedule_name' => $scheduleData['name'],
            ]);

            $config->fill($configData);
            $config->save();

            $existingConfigIds[] = $config->id;

            $config->periods()->delete();

            usort($scheduleData['periods'], fn ($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

            foreach ($scheduleData['periods'] as $index => $p) {
                $isBreak = $p['is_break'] ?? false;
                $type = $p['type'] ?? ($isBreak ? 'break' : 'teaching');

                $config->periods()->create([
                    'period_number' => $p['period_number'],
                    'name' => $p['name'],
                    'start_time' => $p['start_time'],
                    'end_time' => $p['end_time'],
                    'is_break' => $isBreak,
                    'type' => $type,
                    'sort_order' => $p['sort_order'] ?? $index,
                ]);
            }
        }

        $configsToDelete = PeriodConfig::where('term_id', $termId)
            ->when(! empty($existingConfigIds), fn ($q) => $q->whereNotIn('id', $existingConfigIds))
            ->get();

        foreach ($configsToDelete as $configToDelete) {
            $configToDelete->periods()->delete();
            $configToDelete->delete();
        }
    }

    private function saveSingleConfig(int $termId, int $sessionId, array $validated): void
    {
        $config = PeriodConfig::updateOrCreate(
            ['term_id' => $termId],
            [
                'academic_session_id' => $sessionId,
                'periods_per_day' => $validated['periods_per_day'] ?? 8,
                'start_day' => $validated['start_day'] ?? 'Monday',
                'end_day' => $validated['end_day'] ?? 'Friday',
                'schedule_name' => $validated['schedule_name'] ?? 'Standard Day',
            ]
        );

        if (isset($validated['periods'])) {
            $config->periods()->delete();

            usort($validated['periods'], fn ($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

            foreach ($validated['periods'] as $index => $p) {
                $config->periods()->create([
                    'period_number' => $p['period_number'],
                    'name' => $p['name'],
                    'start_time' => $p['start_time'],
                    'end_time' => $p['end_time'],
                    'is_break' => $p['is_break'] ?? false,
                    'type' => $p['is_break'] ? 'break' : 'teaching',
                    'sort_order' => $p['sort_order'] ?? $index,
                ]);
            }

            $affectedEntries = Timetable::where('period_config_id', $config->id)
                ->where('is_break', false)
                ->get();

            foreach ($affectedEntries as $entry) {
                $newPeriod = collect($validated['periods'])->first(fn ($p) => $p['period_number'] === $entry->period_number);
                if ($newPeriod) {
                    $entry->update([
                        'start_time' => $newPeriod['start_time'],
                        'end_time' => $newPeriod['end_time'],
                    ]);
                }
            }
        }
    }

    public function storeCell(Request $request)
    {
        $currentTerm = $this->resolveCurrentTerm();

        $data = $request->validate([
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'day' => ['required', 'in:'.implode(',', self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
        ]);

        $data['term_id'] = $data['term_id'] ?? $currentTerm?->id;
        $data['academic_session_id'] = $data['academic_session_id'] ?? $currentTerm?->academic_session_id;
        $data['is_locked'] = false;
        $data['is_manual'] = true;

        $timetable = new Timetable($data);

        $conflicts = Timetable::validateTimetableEntry($timetable);

        if ($conflicts->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot save: schedule conflict.',
                'errors' => $conflicts->toArray(),
            ], 409);
        }

        $timetable->save();

        $this->audit($request, 'timetable.cell.created', Timetable::class, $timetable->id, null, $data);

        return response()->json([
            'success' => true,
            'message' => 'Lesson saved successfully.',
            'entry' => $timetable->load(['classSubject.class', 'classSubject.subject', 'teacher.user']),
        ]);
    }

    public function updateCell(Request $request, Timetable $timetable)
    {
        $data = $request->validate([
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'day' => ['required', 'in:'.implode(',', self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
        ]);

        $oldValue = $timetable->toArray();

        $model = clone $timetable;
        $model->fill($data);
        $conflicts = Timetable::validateTimetableEntry($model);

        if ($conflicts->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update: schedule conflict.',
                'errors' => $conflicts->toArray(),
            ], 409);
        }

        $timetable->update($data);

        $this->audit($request, 'timetable.cell.updated', Timetable::class, $timetable->id, $oldValue, $timetable->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully.',
            'entry' => $timetable->load(['classSubject.class', 'classSubject.subject', 'teacher.user']),
        ]);
    }

    public function destroyCell(Request $request, Timetable $timetable)
    {
        $this->audit($request, 'timetable.cell.deleted', Timetable::class, $timetable->id, $timetable->toArray(), null);

        $timetable->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson removed successfully.',
        ]);
    }

    public function create()
    {
        $currentTerm = $this->resolveCurrentTerm();
        $config = $this->resolveConfig($currentTerm);

        $data = $this->loadData($config, $currentTerm);

        return view('admin.timetable.create', $data);
    }

    public function store(Request $request)
    {
        $currentTerm = $this->resolveCurrentTerm();

        $data = $request->validate([
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'day' => ['required', 'in:'.implode(',', self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
            'is_locked' => ['nullable', 'boolean'],
        ]);

        $data['term_id'] = $data['term_id'] ?? $currentTerm?->id;
        $data['academic_session_id'] = $data['academic_session_id'] ?? $currentTerm?->academic_session_id;
        $data['is_locked'] = $data['is_locked'] ?? false;
        $data['is_manual'] = true;

        $timetable = new Timetable($data);

        $conflicts = Timetable::validateTimetableEntry($timetable);

        if ($conflicts->isNotEmpty()) {
            return redirect()->route('admin.timetable.index')->withInput()->withErrors($conflicts->toArray());
        }

        $timetable->save();

        $this->audit($request, 'timetable.manual_created', Timetable::class, $timetable->id, null, $data);

        return redirect()->route('admin.timetable.index')->with('status', 'Timetable entry created successfully.');
    }

    public function edit(Timetable $timetable)
    {
        $currentTerm = $this->resolveCurrentTerm();
        $config = $this->resolveConfig($currentTerm);

        $data = $this->loadData($config, $currentTerm);
        $data['timetable'] = $timetable->load(['classSubject.class', 'classSubject.subject', 'teacher.user', 'term', 'academicSession', 'periodConfig.periods']);

        return view('admin.timetable.edit', $data);
    }

    public function update(Request $request, Timetable $timetable)
    {
        $data = $request->validate([
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'day' => ['required', 'in:'.implode(',', self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
            'is_locked' => ['nullable', 'boolean'],
        ]);

        $wasManual = $timetable->exists;
        $data['is_locked'] = $data['is_locked'] ?? false;
        if ($wasManual) {
            $data['is_manual'] = true;
        }

        $oldValue = $timetable->toArray();

        $model = clone $timetable;
        $model->fill($data);
        $conflicts = Timetable::validateTimetableEntry($model);

        if ($conflicts->isNotEmpty()) {
            return redirect()->route('admin.timetable.index')->withInput()->withErrors($conflicts->toArray());
        }

        $timetable->update($data);

        $this->audit($request, 'timetable.updated', Timetable::class, $timetable->id, $oldValue, $timetable->toArray());

        return redirect()->route('admin.timetable.index')->with('status', 'Timetable entry updated successfully.');
    }

    public function destroy(Request $request, Timetable $timetable)
    {
        $this->audit($request, 'timetable.deleted', Timetable::class, $timetable->id, $timetable->toArray(), null);

        $timetable->delete();

        return redirect()->route('admin.timetable.index')->with('status', 'Timetable entry removed successfully.');
    }

    public function move(Request $request, Timetable $timetable)
    {
        $request->validate([
            'day' => ['required', 'in:'.implode(',', self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $model = clone $timetable;
        $model->fill([
            'day' => $request->input('day'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
        ]);

        $conflicts = Timetable::validateTimetableEntry($model);

        $isAjax = $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        if ($conflicts->isNotEmpty()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot move: schedule conflict.',
                    'errors' => $conflicts->toArray(),
                ], 409);
            }

            return redirect()->route('admin.timetable.index')->withInput()->withErrors($conflicts->toArray());
        }

        $oldValue = $timetable->toArray();

        $timetable->update([
            'day' => $request->input('day'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
        ]);

        $this->audit($request, 'timetable.moved', Timetable::class, $timetable->id, $oldValue, $timetable->toArray());

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Entry moved to '.$request->input('day').' '.$request->input('start_time').'.',
                'end_time' => Carbon::parse($request->input('end_time'))->format('H:i'),
            ]);
        }

        return redirect()->route('admin.timetable.index')->with('status', 'Entry moved to '.$request->input('day').' '.$request->input('start_time').'.');
    }

    public function swap(Request $request)
    {
        $isAjax = $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        $validator = \Validator::make($request->all(), [
            'entry_a_id' => ['required', 'exists:timetables,id'],
            'entry_b_id' => ['required', 'exists:timetables,id'],
        ]);

        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request.',
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            return redirect()->route('admin.timetable.index')->withInput()->withErrors($validator);
        }

        $validated = $validator->validated();

        $entryA = Timetable::findOrFail($validated['entry_a_id']);
        $entryB = Timetable::findOrFail($validated['entry_b_id']);

        if ($entryA->id === $entryB->id) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot swap an entry with itself.',
                ], 400);
            }

            return redirect()->route('admin.timetable.index')->withErrors('Cannot swap an entry with itself.');
        }

        $conflicts = Timetable::validateSwap($entryA, $entryB);

        if ($conflicts->isNotEmpty()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot swap: schedule conflict.',
                    'errors' => $conflicts->toArray(),
                ], 409);
            }

            return redirect()->route('admin.timetable.index')->withInput()->withErrors($conflicts->toArray());
        }

        DB::transaction(function () use ($request, $entryA, $entryB) {
            $oldA = $entryA->toArray();
            $oldB = $entryB->toArray();

            $entryA->update([
                'day' => $entryB->day,
                'start_time' => Carbon::parse($entryB->start_time)->format('H:i'),
                'end_time' => Carbon::parse($entryB->end_time)->format('H:i'),
            ]);

            $entryB->update([
                'day' => $oldA['day'],
                'start_time' => Carbon::parse($oldA['start_time'])->format('H:i'),
                'end_time' => Carbon::parse($oldA['end_time'])->format('H:i'),
            ]);

            $this->audit($request, 'timetable.swapped', Timetable::class, $entryA->id, $oldA, $entryA->toArray());
            $this->audit($request, 'timetable.swapped', Timetable::class, $entryB->id, $oldB, $entryB->toArray());
        });

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Entries swapped successfully.',
            ]);
        }

        return redirect()->route('admin.timetable.index')->with('status', 'Entries swapped successfully.');
    }
}
