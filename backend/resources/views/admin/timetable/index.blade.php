<x-layouts.app title="Timetable">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="/admin">Admin</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Configure periods, generate schedules, and manage class timetables.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    @if($errors->any())
        <x-ui.alert variant="error" class="mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <div class="space-y-4">
        {{-- A. Configuration Area --}}
        <x-ui.card class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Period Configuration</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    @if($config)
                        {{ $currentTerm?->name ?? 'N/A' }} ({{ $currentTerm?->academicSession?->name ?? 'N/A' }})
                    @else
                        <span class="text-danger-500">No current term configured.</span>
                    @endif
                </p>
            </div>
            @if($config)
            <form method="POST" action="{{ route('admin.timetable.periods.store') }}" id="period-config-form">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Periods per Day</label>
                        <input type="number" name="periods_per_day" min="1" max="20" value="{{ $config->periods_per_day }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Start Day</label>
                        <select name="start_day" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                            @foreach(App\Http\Controllers\Admin\TimetableController::DAYS as $day)
                                <option value="{{ $day }}" {{ $config->start_day == $day ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">End Day</label>
                        <select name="end_day" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                            @foreach(App\Http\Controllers\Admin\TimetableController::DAYS as $day)
                                <option value="{{ $day }}" {{ $config->end_day == $day ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="periods-list" class="space-y-1 mb-2">
                    @foreach($config->periods->sortBy('sort_order') as $period)
                        @include('admin.timetable.partials.period-input', ['period' => $period, 'index' => $loop->index, 'showDelete' => false])
                    @endforeach
                </div>
                <div class="flex justify-between items-center">
                    <button type="button" id="add-period-btn" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
                        + Add Period
                    </button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1 rounded shadow-sm text-sm">Save</button>
                </div>
            </form>
            @endif
        </x-ui.card>

        {{-- B. Generation Area --}}
        <x-ui.card class="p-4">
            <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Generate Timetable</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Generate a weekly schedule based on teacher assignments and class subjects.</p>
            </div>
            <div class="p-4">
                @if($config && $config->periods->count() > 0)
                    <form method="POST" action="{{ route('admin.timetable.generate') }}" id="generate-form">
                        @csrf
                        <div class="flex gap-3">
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 12a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                                Generate Timetable
                            </button>
                            @if(\App\Models\Timetable::where('term_id', $currentTerm?->id ?? 0)->exists())
                                <button type="button" onclick="if(confirm('Regenerating will replace existing generated entries (except locked/manual ones). Continue?')) { document.getElementById('generate-form').submit(); }" class="bg-neutral-600 hover:bg-neutral-700 text-white font-medium px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm">
                                    Regenerate
                                </button>
                            @endif
                        </div>
                    </form>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1.5">Preview generated schedule. Review and confirm before saving.</p>
                @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Configure periods first to enable timetable generation.</p>
                @endif

                @if($previewData)
                    <div class="mt-3 p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded border border-neutral-200 dark:border-dark-border">
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Generation Preview</h4>
                        @if($previewData['entries']->isNotEmpty())
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                                {{ $previewData['entries']->count() }} timetable entries generated.
                            </p>

                            @php
                                $allDays = App\Http\Controllers\Admin\TimetableController::DAYS;
                                $activePeriods = $config?->periods?->where('is_break', false)->sortBy('sort_order') ?? collect();

                                $entriesByClassAndSlot = $previewData['entries']->groupBy('class_name');
                            @endphp

                            @foreach($classes as $cls)
                                @php
                                    $classEntries = $entriesByClassAndSlot[$cls->name] ?? collect();
                                @endphp

                                @if($classEntries->isNotEmpty())
                                    <div class="mb-6">
                                        <h5 class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 mb-2 uppercase">{{ $cls->name }} - Generated Schedule</h5>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs border border-neutral-200 dark:border-dark-border">
                                                <thead class="bg-neutral-200 dark:bg-neutral-800">
                                                    <tr>
                                                        <th class="px-2 py-1 text-left border-r">Time</th>
                                                        @foreach($allDays as $d)
                                                            <th class="px-2 py-1 text-center border-r last:border-0">{{ $d }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-dark-surface">
                                                    @php
                                                        $entriesBySlot = collect();
                                                        foreach ($classEntries as $entry) {
                                                            $key = $entry['day'] . '|' . \Carbon\Carbon::parse($entry['start_time'])->format('H:i');
                                                            $entriesBySlot[$key] = $entry;
                                                        }
                                                    @endphp
                                                    @foreach($activePeriods as $period)
                                                        @php
                                                            $periodStart = \Carbon\Carbon::parse($period->start_time)->format('H:i');
                                                            $startTime = \Carbon\Carbon::parse($period->start_time)->format('g:i A');
                                                            $endTime = \Carbon\Carbon::parse($period->end_time)->format('g:i A');
                                                        @endphp
                                                        <tr class="border-b border-neutral-200 dark:border-dark-border">
                                                            <td class="px-2 py-1 text-neutral-600 dark:text-neutral-400 border-r">{{ $startTime }} - {{ $endTime }}</td>
                                                            @foreach($allDays as $d)
                                                                @php
                                                                    $key = $d . '|' . $periodStart;
                                                                    $entry = $entriesBySlot[$key] ?? null;
                                                                @endphp
                                                                <td class="px-2 py-1 text-center border-r last:border-0">
                                                                    @if($entry)
                                                                        <div class="font-medium text-neutral-900 dark:text-white">{{ $entry['subject'] }}</div>
                                                                        <div class="text-neutral-500 dark:text-neutral-400">{{ $entry['teacher_name'] }}</div>
                                                                    @else
                                                                        <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if($previewData['warnings']->isNotEmpty())
                                <div class="mb-3">
                                    @foreach($previewData['warnings'] as $w)
                                        <p class="text-xs text-warning-600 dark:text-warning-400">
                                            {{ $w['class'] }} / {{ $w['subject'] }}: {{ $w['message'] }}
                                        </p>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.timetable.confirm-generate') }}">
                                @csrf
                                <button type="submit" class="bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm">Confirm & Save</button>
                            </form>
                        @else
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">No entries generated. Check if class subjects and teacher assignments exist.</p>
                        @endif
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- C. Filters --}}
        <x-ui.card class="p-3">
            <form method="GET" action="{{ route('admin.timetable.index') }}" class="grid grid-cols-1 sm:grid-cols-8 gap-2 items-end">
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Session</label>
                    <select name="session_id" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ $request->input('session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Term</label>
                    <select name="term_id" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ $request->input('term_id', $currentTerm?->id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Class</label>
                    <select name="class_id" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $request->input('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Teacher</label>
                    <select name="teacher_id" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $request->input('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->user?->name ?? 'Unnamed' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Subject</label>
                    <select name="subject_id" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ $request->input('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-0.5">Day</label>
                    <select name="day" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach(App\Http\Controllers\Admin\TimetableController::DAYS as $d)
                            <option value="{{ $d }}" {{ $request->input('day') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-1">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1 rounded shadow-sm h-8 text-sm">Apply</button>
                    <button type="button" onclick="window.location.href='{{ route('admin.timetable.index') }}'" class="text-xs text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 h-8 flex items-end">Clear</button>
                </div>
            </form>
        </x-ui.card>

        {{-- D. Weekly Schedule by Class --}}
        <x-ui.card class="p-3">
            <div class="py-2 border-b border-neutral-200 dark:border-dark-border mb-2">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Weekly Schedule by Class</h3>
            </div>
            <div class="p-2" id="class-grids">
                @php
                    $allDays = App\Http\Controllers\Admin\TimetableController::DAYS;
                    $activePeriods = $config?->periods?->where('is_break', false)->sortBy('sort_order') ?? collect();
                    $filteredClasses = $request->input('class_id') ? $classes->where('id', $request->input('class_id')) : $classes;
                @endphp

                @if($timetable->isEmpty())
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-8">No timetable entries found. Generate a timetable or add entries manually.</p>
                @else
                    @php
                        $entriesByClassAndSlot = $timetable->groupBy(function($item) {
                            return optional($item->classSubject)->class_id ?? 0;
                        });
                    @endphp

                    @foreach($filteredClasses as $cls)
                        @php
                            $classEntries = $entriesByClassAndSlot[$cls->id] ?? collect();
                            $entryCount = $classEntries->count();
                        @endphp

                        @if($entryCount > 0)
                            @php
                                $isExpanded = true;
                                $entriesBySlot = collect();
                                foreach ($classEntries as $entry) {
                                    $key = $entry->day . '|' . \Carbon\Carbon::parse($entry->start_time)->format('H:i');
                                    $entriesBySlot[$key] = $entry;
                                }
                            @endphp
                            <div class="mb-4">
                                <button type="button"
                                    class="class-toggle w-full flex items-center gap-2 text-left mb-2"
                                    data-class-id="{{ $cls->id }}">
                                    <svg class="h-4 w-4 text-neutral-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    <span class="text-base font-semibold text-neutral-900 dark:text-white">{{ $cls->name }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">({{ $entryCount }} entries)</span>
                                </button>

                                <div class="class-grid-content overflow-x-auto">
                                    <table class="min-w-full text-xs border border-neutral-200 dark:border-dark-border">
                                        <thead class="bg-neutral-50 dark:bg-neutral-800">
                                            <tr>
                                                <th class="px-2 py-1 text-left border-r">Time</th>
                                                @foreach($allDays as $d)
                                                    <th class="px-2 py-1 text-center border-r last:border-0">{{ $d }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-200 dark:divide-dark-border">
                                            @foreach($activePeriods as $period)
                                                @php
                                                    $periodStart = \Carbon\Carbon::parse($period->start_time)->format('H:i');
                                                    $startTime = \Carbon\Carbon::parse($period->start_time)->format('g:i A');
                                                    $endTime = \Carbon\Carbon::parse($period->end_time)->format('g:i A');
                                                @endphp
                                                <tr>
                                                    <td class="px-2 py-1 text-neutral-600 dark:text-neutral-400 border-r">{{ $startTime }} - {{ $endTime }}</td>
                                                    @foreach($allDays as $d)
                                                        @php
                                                            $key = $d . '|' . $periodStart;
                                                            $slot = $entriesBySlot[$key] ?? null;
                                                        @endphp
                                                        <td class="px-1 py-1 text-center border-r last:border-0">
                                                            @if($slot)
                                                                @php $isLocked = $slot->is_locked; @endphp
                                                                <div class="{{ $isLocked ? 'bg-warning-50 dark:bg-warning-900/20' : 'bg-neutral-50 dark:bg-neutral-800/50' }} p-1 rounded text-left group relative">
                                                                    <div class="font-medium text-neutral-900 dark:text-white text-xs">
                                                                        {{ $slot->classSubject->subject->name ?? 'N/A' }}
                                                                    </div>
                                                                    <div class="text-neutral-500 dark:text-neutral-400 text-xs">
                                                                        {{ $slot->teacher?->user?->name ?? 'Not assigned' }}
                                                                    </div>
                                                                    @if($isLocked)
                                                                        <span class="text-xs text-warning-600 dark:text-warning-400">Locked</span>
                                                                    @endif
                                                                    <div class="absolute top-0.5 right-0.5 opacity-0 group-hover:opacity-100 flex gap-1">
                                                                        <a href="{{ route('admin.timetable.edit', $slot) }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-4-9l-6 6v2h2l6-6 2-2V7a2 2 0 00-2-2h-2z"/></svg>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </x-ui.card>

        {{-- Manual Entry --}}
        <x-ui.card class="p-3">
            <div class="py-1">
                <a href="{{ route('admin.timetable.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1.5 rounded shadow-sm text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Entry Manually
                </a>
            </div>
        </x-ui.card>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.class-toggle').forEach(function(btn) {
            var content = btn.nextElementSibling;
            var icon = btn.querySelector('svg');
            if (!content.classList.contains('hidden')) {
                icon.style.transform = 'rotate(180deg)';
            }

            btn.addEventListener('click', function() {
                var content = this.nextElementSibling;
                var icon = this.querySelector('svg');

                if (content.classList.contains('hidden')) {
                    content.classList.remove('hidden');
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    content.classList.add('hidden');
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });

        let periodCounter = 0;
        const config = @json($config?->toArray() ?? null);
        if (config && config.periods && config.periods.length) {
            periodCounter = config.periods.length;
        }

        const addPeriodBtn = document.getElementById('add-period-btn');
        if (addPeriodBtn) {
            addPeriodBtn.addEventListener('click', function() {
                periodCounter++;
                const container = document.getElementById('periods-list');
                const div = document.createElement('div');
                div.className = 'grid grid-cols-5 gap-2 items-end p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg';
                div.innerHTML = `
                    <div class="col-span-2">
                        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Name</label>
                        <input type="text" name="periods[${periodCounter}][name]" placeholder="e.g. Period 9" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                    </div>
                    <input type="hidden" name="periods[${periodCounter}][period_number]" value="${periodCounter}">
                    <input type="hidden" name="periods[${periodCounter}][sort_order]" value="${periodCounter}">
                    <div>
                        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Start</label>
                        <input type="time" name="periods[${periodCounter}][start_time]" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">End</label>
                        <input type="time" name="periods[${periodCounter}][end_time]" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                            <input type="checkbox" name="periods[${periodCounter}][is_break]" value="1" class="rounded border-neutral-300 dark:border-dark-border">
                            Break
                        </label>
                    </div>
                `;
                container.appendChild(div);
            });
        }
    </script>
    @endpush
</x-layouts.app>