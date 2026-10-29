<x-layouts.app title="Timetable Configuration">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('admin.dashboard') }}">Admin</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item href="{{ route('admin.timetable.index') }}">Timetable</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Configuration</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Timetable Configuration</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            Create multiple named schedules with different lesson durations, day assignments, breaks, and activities.
        </p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <div class="flex flex-col gap-2">
                <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">Please fix the following errors:</h4>
                <ul class="list-disc list-inside text-sm text-danger-700 dark:text-danger-400 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </x-ui.alert>
    @endif

    <x-ui.card class="p-6">
        <form method="POST" action="{{ route('admin.timetable.config.save') }}" id="config-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">Academic Session</label>
                    <select name="academic_session_id" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-all focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ $session->id == $currentTerm?->academic_session_id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">Term</label>
                    <select name="term_id" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-all focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ $term->id == $currentTerm?->id ? 'selected' : '' }}>
                                {{ $term->name }} ({{ $term->academicSession?->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="button" onclick="window.location.href='{{ route('admin.timetable.index') }}'" class="bg-neutral-600 hover:bg-neutral-700 text-white font-medium px-4 py-2 rounded-xl shadow-sm text-sm">
                        Back to Timetable
                    </button>
                </div>
            </div>

            @php
                $allDays = \App\Http\Controllers\Admin\TimetableController::DAYS;
                $configs = $configs ?? collect([$config]);
                $configs = $configs->values();
            @endphp

            <div class="mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">School Schedules</h3>
                    <button type="button" id="add-schedule-btn"
                        class="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-xl shadow-sm text-sm md:text-xs w-full sm:w-auto">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Schedule
                    </button>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    Create multiple named schedules. Each schedule can apply to specific days of the week or all days. The first matching schedule (in order) is used for each day.
                </p>
            </div>

            <div id="schedules-container" class="space-y-6">
                @if($configs && $configs->isNotEmpty())
                    @foreach($configs as $scheduleIndex => $schedule)
                        @include('admin.timetable.partials.config-schedule-card', ['index' => $scheduleIndex, 'schedule' => $schedule, 'allDays' => $allDays])
                    @endforeach
                @endif
            </div>

            <input type="hidden" name="schedules_count" id="schedules_count" value="{{ $configs?->count() ?? 0 }}">

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-neutral-200 dark:border-dark-border">
                <button type="button" onclick="window.location.href='{{ route('admin.timetable.index') }}'"
                    class="bg-neutral-600 hover:bg-neutral-700 text-white font-medium px-6 py-2.5 rounded-xl shadow-sm text-sm">
                    Cancel
                </button>
                <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-8 py-2.5 rounded-xl shadow-sm text-sm">
                    Save Configuration
                </button>
            </div>
        </form>
    </x-ui.card>

    <div id="schedule-template" class="schedule-card border border-neutral-200 dark:border-dark-border rounded-xl p-4 relative hidden" data-schedule-index="__INDEX__">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1 pr-3">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Schedule Name</label>
                <input type="text" name="schedules[__INDEX__][name]"
                    placeholder="e.g. Standard Day, Assembly Day"
                    class="block w-full px-3.5 py-2 text-neutral-900 dark:text-white bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border rounded-xl shadow-sm placeholder-neutral-400 dark:placeholder-neutral-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 sm:text-sm">

                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mt-4 mb-1.5">Applies To</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-2 mb-3">
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-neutral-200 dark:border-dark-border hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-all">
                        <input type="checkbox"
                            name="schedules[__INDEX__][select_all_days]"
                            value="1"
                            class="schedule-select-all rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500"
                            checked>
                        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Select All</span>
                    </label>
                    @foreach($allDays as $day)
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-neutral-200 dark:border-dark-border hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-all">
                            <input type="checkbox"
                                name="schedules[__INDEX__][days][]"
                                value="{{ $day }}"
                                class="schedule-day-checkbox rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
                            <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">{{ $day }}</span>
                        </label>
                    @endforeach
                </div>

                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Select specific days this schedule applies to, or leave unselected for all days.
                </p>
            </div>

            <button type="button" onclick="removeSchedule(this)"
                class="flex-shrink-0 ml-2 text-neutral-500 dark:text-neutral-400 hover:text-danger-600 dark:hover:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg px-2 py-1 transition-colors"
                title="Remove this schedule">
                Remove
            </button>
        </div>

        <div class="periods-list space-y-3" id="periods-list-__INDEX__">
        </div>

        <input type="hidden" name="schedules[__INDEX__][periods_count]" value="8">
        <button type="button" onclick="addPeriodRow('__INDEX__')" class="mt-3 text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
            + Add Period Row
        </button>
    </div>

    @push('scripts')
    <script>
        let scheduleCounter = 0;

        function addSchedule() {
            const container = document.getElementById('schedules-container');
            const template = document.getElementById('schedule-template');
            const clone = template.cloneNode(true);
            clone.classList.remove('hidden');

            const index = 'sched_' + scheduleCounter++;
            clone.setAttribute('data-schedule-index', index);

            clone.innerHTML = clone.innerHTML.replace(/__INDEX__/g, index);

            container.appendChild(clone);
            document.getElementById('schedules_count').value = scheduleCounter;

            attachSelectAllHandler(clone);
            attachPeriodAutoFill(clone);
        }

        function attachSelectAllHandler(card) {
            const selectAll = card.querySelector('.schedule-select-all');
            const checkboxes = card.querySelectorAll('.schedule-day-checkbox');

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });

                function updateSelectAllState() {
                    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    selectAll.indeterminate = anyChecked && !allChecked;
                }

                checkboxes.forEach(cb => cb.addEventListener('change', updateSelectAllState));
                updateSelectAllState();
            }
        }

        document.querySelectorAll('.schedule-card').forEach(attachSelectAllHandler);

        function removeSchedule(button) {
            const card = button.closest('.schedule-card');
            if (card) {
                card.remove();
            }
        }

        function addPeriodRow(scheduleIndex) {
            const container = document.getElementById('periods-list-' + scheduleIndex);
            const index = container.children.length;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 items-end gap-3 p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-dark-border';
            row.innerHTML = `
                <div class="sm:col-span-2 md:col-span-1">
                    <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Name</label>
                    <input type="text" name="schedules[${scheduleIndex}][periods][${index}][name]" placeholder="e.g. Period ${index + 1}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Start</label>
                    <input type="time" name="schedules[${scheduleIndex}][periods][${index}][start_time]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">End</label>
                    <input type="time" name="schedules[${scheduleIndex}][periods][${index}][end_time]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Type</label>
                    <select name="schedules[${scheduleIndex}][periods][${index}][type]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="teaching">Teaching</option>
                        <option value="break">Break</option>
                        <option value="activity">Activity</option>
                    </select>
                </div>
                <div class="md:col-span-1 flex items-end pb-0.5">
                    <label class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400 cursor-pointer">
                        <input type="checkbox" name="schedules[${scheduleIndex}][periods][${index}][is_break]" value="1" class="rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
                        Is Break
                    </label>
                </div>
                <input type="hidden" name="schedules[${scheduleIndex}][periods][${index}][period_number]" value="${index + 1}">
                <input type="hidden" name="schedules[${scheduleIndex}][periods][${index}][sort_order]" value="${index}">
            `;
            container.appendChild(row);
        }

        function attachPeriodAutoFill(card) {
            const periodsList = card.querySelector('.periods-list');
            if (periodsList) {
                periodsList.addEventListener('input', function(e) {
                    if (e.target.name && e.target.name.includes('[start_time]')) {
                        const row = e.target.closest('.grid');
                        const endInput = row.querySelector('input[name$="[end_time]"]');
                        const startVal = e.target.value;
                        if (startVal && !endInput.value) {
                            const [h, m] = startVal.split(':').map(Number);
                            const endMinutes = h * 60 + m + 40;
                            const endH = Math.floor(endMinutes / 60) % 24;
                            const endM = endMinutes % 60;
                            endInput.value = String(endH).padStart(2, '0') + ':' + String(endM).padStart(2, '0');
                        }
                    }
                });
            }
        }

        document.querySelectorAll('.schedule-card').forEach(attachPeriodAutoFill);

        document.getElementById('add-schedule-btn').addEventListener('click', addSchedule);
    </script>
    @endpush
</x-layouts.app>
