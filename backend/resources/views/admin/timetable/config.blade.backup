<x-layouts.app title="Timetable Configuration">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item href="{{ route('admin.timetable.index') }}">Timetable</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Configuration</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Timetable Configuration</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            Configure school days, periods, times, and breaks for the selected term.
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
                            <option value="{{ $session->id }}" {{ ($config->academic_session_id ?? $currentTerm?->academic_session_id) == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">Term</label>
                    <select name="term_id" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-all focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ ($config->term_id ?? $currentTerm?->id) == $term->id ? 'selected' : '' }}>
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

            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">School Days</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                    @php
                        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                        $startDay = $config->start_day ?? 'Monday';
                        $endDay = $config->end_day ?? 'Friday';
                        $startIdx = array_search($startDay, $allDays) ?? 0;
                        $endIdx = array_search($endDay, $allDays) ?? 4;
                        $activeDays = array_slice($allDays, $startIdx, $endIdx - $startIdx + 1);
                    @endphp
                    @foreach($allDays as $day)
                        <label class="flex items-center gap-2 p-3 rounded-xl border-2 {{ in_array($day, $activeDays) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-neutral-200 dark:border-dark-border hover:border-primary-300' }} cursor-pointer transition-all">
                            <input type="checkbox" name="active_days[]" value="{{ $day }}" {{ in_array($day, $activeDays) ? 'checked' : '' }} class="rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-medium {{ in_array($day, $activeDays) ? 'text-primary-700 dark:text-primary-400 font-bold' : 'text-neutral-600 dark:text-neutral-400' }}">{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Periods</h3>
                    <button type="button" id="add-period-row" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
                        + Add Period
                    </button>
                </div>

                <div id="period-rows" class="space-y-3">
                    @php
                        $periods = $config->periods->sortBy('sort_order');
                        $periodIndex = 0;
                    @endphp
                    @foreach($periods as $period)
                        @include('admin.timetable.partials.config-period-row', ['period' => $period, 'index' => $periodIndex++])
                    @endforeach
                </div>

                <input type="hidden" name="periods_per_day" value="{{ $config->periods->count() }}">
                <input type="hidden" name="start_day" value="{{ $config->start_day ?? 'Monday' }}">
                <input type="hidden" name="end_day" value="{{ $config->end_day ?? 'Friday' }}">
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Preview</h3>
                </div>
                <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-xl p-4 border border-neutral-200 dark:border-dark-border overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-neutral-500 dark:text-neutral-400 font-medium">Period</th>
                                <th class="px-3 py-2 text-left text-neutral-500 dark:text-neutral-400 font-medium">Time</th>
                                <th class="px-3 py-2 text-left text-neutral-500 dark:text-neutral-400 font-medium">Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border">
                            @php
                                $previewPeriods = $config->periods->sortBy('sort_order');
                            @endphp
                            @foreach($previewPeriods as $period)
                                @php
                                    $startTime = \Carbon\Carbon::parse($period->start_time)->format('g:i A');
                                    $endTime = \Carbon\Carbon::parse($period->end_time)->format('g:i A');
                                    $type = $period->is_break ? 'Break' : 'Teaching';
                                @endphp
                                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800/50">
                                    <td class="px-3 py-2 text-neutral-900 dark:text-white">{{ $period->name ?? 'Period ' . $period->period_number }}</td>
                                    <td class="px-3 py-2 text-neutral-900 dark:text-white">{{ $startTime }} – {{ $endTime }}</td>
                                    <td class="px-3 py-2 text-xs font-medium {{ $period->is_break ? 'text-warning-600 dark:text-warning-400' : 'text-success-600 dark:text-success-400' }}">{{ $type }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6">
                <button type="button" onclick="window.location.href='{{ route('admin.timetable.index') }}'" class="bg-neutral-600 hover:bg-neutral-700 text-white font-medium px-6 py-2.5 rounded-xl shadow-sm text-sm">
                    Cancel
                </button>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-8 py-2.5 rounded-xl shadow-sm text-sm">
                    Save Configuration
                </button>
            </div>
        </form>
    </x-ui.card>

    @push('scripts')
    <script>
        document.getElementById('add-period-row').addEventListener('click', function() {
            const container = document.getElementById('period-rows');
            const index = container.children.length;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-dark-border animate-in fade-in slide-in-from-bottom-2 duration-300';
            row.innerHTML = `
                <div>
                    <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Period Name</label>
                    <input type="text" name="periods[${index}][name]" placeholder="e.g. Period ${index + 1}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Start Time</label>
                    <input type="time" name="periods[${index}][start_time]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">End Time</label>
                    <input type="time" name="periods[${index}][end_time]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400 cursor-pointer">
                        <input type="checkbox" name="periods[${index}][is_break]" value="1" class="rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
                        Break
                    </label>
                </div>
                <input type="hidden" name="periods[${index}][period_number]" value="${index + 1}">
                <input type="hidden" name="periods[${index}][sort_order]" value="${index}">
                <input type="hidden" name="periods[${index}][is_break]" value="0">
            `;
            container.appendChild(row);
        });

        document.getElementById('period-rows').addEventListener('input', function(e) {
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
    </script>
    @endpush
</x-layouts.app>
