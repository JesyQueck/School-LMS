    @php
        $days = [];
        $dayRules = $schedule->day_rules;
        $selectAlldays = false;

        if (is_string($dayRules)) {
            try {
                $decoded = json_decode($dayRules, true);
                $dayRules = is_array($decoded) ? $decoded : [];
            } catch (\Throwable $e) {
                $dayRules = [];
            }
        }

        if (is_array($dayRules) && !empty($dayRules)) {
            $days = array_values(array_filter($dayRules, fn ($v) => is_string($v)));
        } elseif ($schedule->day_of_week && is_string($schedule->day_of_week) && $schedule->day_of_week !== 'all') {
            $days = [$schedule->day_of_week];
        } else {
            $selectAlldays = true;
        }

        $scheduleName = $schedule->schedule_name ?? ($schedule->name ?? 'Schedule ' . ($index + 1));
    @endphp

    <div class="schedule-card border border-neutral-200 dark:border-dark-border rounded-xl p-4 relative" data-schedule-index="{{ $index }}">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1 pr-3">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Schedule Name</label>
                <input type="text" name="schedules[{{ $index }}][name]"
                    value="{{ $scheduleName }}"
                    placeholder="e.g. Standard Day, Assembly Day"
                    class="block w-full px-3.5 py-2 text-neutral-900 dark:text-white bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border rounded-xl shadow-sm placeholder-neutral-400 dark:placeholder-neutral-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 sm:text-sm">

                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mt-4 mb-1.5">Applies To</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-2 mb-3">
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-neutral-200 dark:border-dark-border hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-all">
                        <input type="checkbox"
                            name="schedules[{{ $index }}][select_all_days]"
                            value="1"
                            class="schedule-select-all rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500"
                            {{ $selectAlldays ? 'checked' : '' }}>
                        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Select All</span>
                    </label>
                    @foreach($allDays as $day)
                        @php
                            $checked = in_array($day, $days) || empty($days) || $selectAlldays;
                        @endphp
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-neutral-200 dark:border-dark-border hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-all">
                            <input type="checkbox"
                                name="schedules[{{ $index }}][days][]"
                                value="{{ $day }}"
                                class="schedule-day-checkbox rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500"
                                {{ $checked ? 'checked' : '' }}>
                            <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">{{ $day }}</span>
                        </label>
                    @endforeach
                </div>

            @if(!empty($days))
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Active for: {{ implode(', ', array_filter($days, 'is_string')) }}
                </p>
            @else
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Active for all days
                </p>
            @endif
        </div>

        <button type="button" onclick="removeSchedule(this)"
            class="flex-shrink-0 ml-2 text-neutral-500 dark:text-neutral-400 hover:text-danger-600 dark:hover:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg px-2 py-1 transition-colors"
            title="Remove this schedule">
            Remove
        </button>
    </div>

    <div class="periods-list space-y-3" id="periods-list-{{ $index }}">
        @foreach($schedule->periods as $periodIndex => $period)
            @if($period)
                @include('admin.timetable.partials.config-period-row', ['index' => $index, 'periodIndex' => $periodIndex, 'period' => $period])
            @endif
        @endforeach
    </div>

    <input type="hidden" name="schedules[{{ $index }}][periods_count]" value="{{ $schedule->periods->count() }}">
    <button type="button" onclick="addPeriodRow('{{ $index }}')" class="mt-3 text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
        + Add Period Row
    </button>
</div>
