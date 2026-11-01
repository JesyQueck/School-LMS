    <style>
        .timetable-header { background-color: #f8fafc; }
        .timetable-tbody { background-color: #ffffff; }
        .timetable-row-alt { background-color: #f8fafc; }
        .timetable-cell-border { border-color: #94a3b8; border-width: 3px; }
        .timetable-entry-bg { background-color: #f8fafc; }
        .timetable-break-text { color: #94a3b8; }
        .assign-cell { color: #94a3b8; }
        .assign-cell:hover { color: #1e293b; }
        .dropdown { background-color: #ffffff; border-color: #e2e8f0; }
        .timetable-entry-text { color: #0f172a; }
        .timetable-entry-subtext { color: #64748b; }

        .dark .timetable-header { background-color: #1e293b; }
        .dark .timetable-tbody { background-color: #0f172a; }
        .dark .timetable-row-alt { background-color: #1e293b7a; }
        .dark .timetable-cell-border { border-color: #475569; border-width: 3px; }
        .dark .timetable-tbody { border-top-color: #475569; border-top-width: 3px; }
        .dark .timetable-entry-bg { background-color: #1e293b80; }
        .dark .timetable-break-text { color: #94a3b8; }
        .dark .assign-cell { color: #94a3b8; }
        .dark .assign-cell:hover { color: #e2e8f0; }
        .dark .dropdown { background-color: #1e293b; border-color: #334155; }
        .dark .timetable-entry-text { color: #f1f5f9; }
        .dark .timetable-entry-subtext { color: #94a3b8; }

        /* Shared dark mode theme classes */
        .dark .bg-dark-surface { background-color: #1e293b; }
        .dark .border-dark-border { border-color: #334155; }
        .dark .text-dark-text { color: #f1f5f9; }
        .dark .bg-dark-bg { background-color: #0f172a; }
        .hover\:bg-neutral-800:hover { background-color: #1e293b; }
        .dark .hover\:bg-neutral-800:hover { background-color: #1e293b; }

        /* Gridline thickness for timetable table */
        .timetable-table td, .timetable-table th { border-width: 3px; border-style: solid; }
        .timetable-table td:last-child, .timetable-table th:last-child { border-right: none; }
        .timetable-tbody { border-top-width: 2px; }
    </style>

<x-layouts.app title="Timetable">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">School Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            View and manage all classes across every day. Click any empty cell to assign a subject and teacher.
        </p>
    </div>
<a href="{{ route('admin.timetable.config') }}" class="inline-flex items-center gap-2 bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0a2.34 2.34 0 0 0 3.319 1.915a2.34 2.34 0 0 1 2.33 4.033a2.34 2.34 0 0 0 0 3.831a2.34 2.34 0 0 1-2.33 4.033a2.34 2.34 0 0 0-3.319 1.915a2.34 2.34 0 0 1-4.659 0a2.34 2.34 0 0 0-3.32-1.915a2.34 2.34 0 0 1-2.33-4.033a2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915z"/><circle cx="12" cy="12" r="3"/></g></svg>
        Configure Timetable
    </a>
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

    <div id="timetable-feedback" class="fixed top-20 left-0 right-0 z-50 hidden"></div>

    <div class="space-y-4">
        {{-- B. Filters --}}
<x-ui.card class="p-3">
            <form method="GET" action="{{ route('admin.timetable.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 items-end">
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
                <div class="flex gap-1 h-8 sm:col-span-1 md:col-span-1 lg:col-span-1">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1 rounded shadow-sm h-8 text-sm">Apply</button>
                    <button type="button" onclick="window.location.href='{{ route('admin.timetable.index') }}'" class="text-xs text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 h-8 px-2">Clear</button>
                </div>
            </form>
        </x-ui.card>

        @if($conflicts && $conflicts->isNotEmpty())
            <x-ui.alert variant="warning" class="mb-4">
                <p class="font-semibold mb-1">Schedule Conflicts Detected ({{ $conflicts->count() }})</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach($conflicts as $conflict)
                        <li>{{ $conflict['day'] }} - {{ $conflict['class'] }}: "{{ $conflict['period'] }}" conflicts with "{{ $conflict['existing_period'] }}" at {{ $conflict['time'] }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @php
            $allDays = \App\Http\Controllers\Admin\TimetableController::DAYS;
            $filteredClasses = $request->input('class_id') ? $classes->where('id', $request->input('class_id')) : $classes;
            $filteredClasses = $filteredClasses->sortBy(function ($cls) {
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

            $classSubjectsByClass = [];
            if (isset($classSubjects)) {
                foreach ($classSubjects as $cs) {
                    $cid = $cs->class_id ?? 0;
                    if (!isset($classSubjectsByClass[$cid])) {
                        $classSubjectsByClass[$cid] = [];
                    }
                    $classSubjectsByClass[$cid][] = [
                        'id' => $cs->id,
                        'subject_name' => $cs->subject->name ?? 'Unknown',
                        'teacher_name' => $cs->teacherAssignments->first()?->teacher?->user?->name ?? 'Not assigned',
                        'teacher_id' => $cs->teacherAssignments->first()?->teacher_id ?? null,
                    ];
                }
            }
            $classSubjectsByClassJson = json_encode($classSubjectsByClass);
        @endphp

        @foreach($allDays as $day)
            @php
                $dayPeriods = collect();
                $dayConfigForDay = null;
                if ($configs) {
                    foreach ($configs as $c) {
                        if ($c->day_of_week === $day) {
                            $dayConfigForDay = $c;
                            break;
                        }
                        $dayRules = $c->day_rules;
                        if (is_string($dayRules)) {
                            $dayRules = json_decode($dayRules, true);
                            $dayRules = is_array($dayRules) ? $dayRules : null;
                        }
                        if (is_array($dayRules) && in_array($day, $dayRules)) {
                            $dayConfigForDay = $c;
                            break;
                        }
                    }
                }
                if ($dayConfigForDay) {
                    $dayPeriods = $dayConfigForDay->periods->sortBy('sort_order');
                }
                if ($dayPeriods->isEmpty()) {
                    continue;
                }
            @endphp

            <x-ui.card class="p-3 mt-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-md font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                        {{ $day }}
                        @if($dayConfigForDay && $dayConfigForDay->schedule_name)
                            <span class="text-xs text-neutral-500 dark:text-neutral-400 font-normal">({{ $dayConfigForDay->schedule_name }})</span>
                        @endif
                    </h4>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $dayPeriods->count() }} periods</span>
                </div>

                @if($dayPeriods->isEmpty())
                    <p class="text-sm text-neutral-400 dark:text-neutral-500">No periods configured for this day.</p>
                @else
                    <div class="overflow-x-auto">
                                <table class="min-w-full text-xs timetable-table timetable-cell-border">
                                    <thead class="timetable-header">
                                        <tr>
                                            <th class="px-3 py-2 text-left timetable-cell-border font-medium text-neutral-700 dark:text-neutral-300">Class</th>
                                            @foreach($dayPeriods as $dp)
                                                <th class="px-2 py-1 text-center last:border-r-0 timetable-cell-border font-medium text-neutral-700 dark:text-neutral-300 min-w-[90px]">
                                                    <div>{{ $dp->name }}</div>
                                                    <div class="text-xs text-neutral-500 dark:text-neutral-400 font-normal">
                                                        {{ \Carbon\Carbon::parse($dp->start_time)->format('g:i A') }}
                                                        <br>
                                                        {{ \Carbon\Carbon::parse($dp->end_time)->format('g:i A') }}
                                                    </div>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="timetable-tbody border-t-2 timetable-cell-border">
                                        @foreach($filteredClasses as $cls)
                                            @php
                                                $classEntries = $gridData[$day][$cls->id] ?? [];
                                            @endphp
                                            <tr class="{{ $loop->odd ? 'timetable-row-alt' : '' }}">
                                                <td class="px-3 py-2 timetable-cell-border font-medium text-neutral-900 dark:text-white">
                                                    {{ $cls->name }}
                                                </td>
                                        @foreach($dayPeriods as $dp)
                                            @php
                                                $dpStart = \Carbon\Carbon::parse($dp->start_time)->format('H:i');
                                                $slot = $classEntries[$dpStart] ?? null;
                                            @endphp
                                                    @if($dp->isBreak() || $dp->isActivity())
                                                    <td class="px-1 py-1 text-center last:border-r-0 timetable-cell-border"
                                                        data-day="{{ $day }}"
                                                        data-class-id="{{ $cls->id }}"
                                                        data-period="{{ $dp->period_number }}"
                                                        data-period-start="{{ $dpStart }}">
                                                        <span class="text-xs timetable-break-text">{{ $dp->name }}</span>
                                                    </td>
                                                @else
                                                    <td class="px-1 py-1 text-center border-r-2 border-b-2 last:border-r-0 drop-cell timetable-cell-border"
                                                        data-day="{{ $day }}"
                                                        data-class-id="{{ $cls->id }}"
                                                        data-period="{{ $dp->period_number }}"
                                                        data-period-start="{{ $dpStart }}">
                                                        @if($slot)
                                                            @php $isLocked = $slot->is_locked; @endphp
                                                            <div class="{{ $isLocked ? 'bg-warning-50' : 'bg-neutral-50 timetable-entry-bg' }} p-1 rounded text-left group relative {{ $isLocked ? '' : 'draggable-entry' }}"
                                                                draggable="{{ $isLocked ? 'false' : 'true' }}"
                                                                data-entry-id="{{ $slot->id }}"
                                                                data-current-day="{{ $slot->day }}"
                                                                data-current-start="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                                                data-current-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}">
                                                                <div class="font-medium timetable-entry-text timetable-entry-text-xs">
                                                                    {{ $slot->classSubject->subject->name ?? 'N/A' }}
                                                                </div>
                                                                <div class="text-xs timetable-entry-subtext">
                                                                    {{ $slot->teacher?->user?->name ?? $slot->classSubject?->teacherAssignments?->first()?->teacher?->user?->name ?? 'Not assigned' }}
                                                                </div>
                                                            @if($isLocked)
                                                                <span class="text-xs text-warning-600 dark:text-warning-400">Locked</span>
                                                            @endif
                                                            <div class="absolute top-0.5 right-0.5 opacity-0 group-hover:opacity-100 flex gap-1">
                                                                <button type="button"
                                                                    class="move-entry-btn text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                                                                    data-entry-id="{{ $slot->id }}"
                                                                    data-current-day="{{ $slot->day }}"
                                                                    data-current-start="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                                                    data-current-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                                                    title="Move to another day/period">
                                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 7l3-3 3 3M8 7l3 3 3-3m-9 9h12m-12 0l3-3 3 3m-3-3v6"/></svg>
                                                                </button>
                                                                    <a href="{{ route('admin.timetable.edit', $slot) }}" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300">
                                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-4-9l-6 6v2h2l6-6 2-2V7a2 2 0 00-2-2h-2z"/></svg>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="delete-entry-btn text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300"
                                                                        data-entry-id="{{ $slot->id }}"
                                                                        title="Delete this lesson">
                                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.133A2 2 0 0116.138 21H7.862a2 2 0 01-1.975-1.847L5.05 7m5 5v5m4-5v5m5-10V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v3m4 0H9"/></svg>
                                                                    </button>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <button type="button"
                                                            class="assign-cell text-neutral-400 hover:text-neutral-700 text-2xl font-bold"
                                                            data-day="{{ $day }}"
                                                            data-class-id="{{ $cls->id }}"
                                                            data-period-start="{{ $dpStart }}">
                                                            +
                                                        </button>
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.card>
        @endforeach

        {{-- Manual Entry --}}
        
    </div>

    {{-- Move Modal --}}
    <div id="move-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-dark-surface rounded-lg shadow-xl w-80 m-4 p-4">
            <h4 class="text-lg font-semibold text-neutral-900 dark:text-white mb-3">Move Entry</h4>
            <form id="move-form" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Day</label>
                    <select name="day" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                        @foreach(App\Http\Controllers\Admin\TimetableController::DAYS as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Start Time</label>
                    <input type="time" name="start_time" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">End Time</label>
                    <input type="time" name="end_time" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="move-cancel" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 px-3 py-1 text-sm">Cancel</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1 rounded shadow-sm text-sm">Move</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="delete-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-dark-surface rounded-lg shadow-xl w-80 m-4 p-4">
            <h4 class="text-lg font-semibold text-neutral-900 dark:text-white mb-3">Delete Lesson</h4>
            <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-4">Are you sure you want to delete this lesson? This action cannot be undone.</p>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="delete-cancel" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 px-3 py-1 text-sm">Cancel</button>
                <button type="button" id="delete-confirm" class="bg-red-600 hover:bg-red-700 text-white font-medium px-3 py-1 rounded shadow-sm text-sm">Delete</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            'use strict';

            var moveModal = document.getElementById('move-modal');
            var moveForm = document.getElementById('move-form');
            var deleteModal = document.getElementById('delete-modal');
            var feedbackEl = document.getElementById('timetable-feedback');
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            var cellStoreUrl = '{{ route('admin.timetable.cell.store') }}';
            var classSubjectsByClass = @json($classSubjectsByClass ?? [], 15, 512);
            var allClassIds = @json($filteredClasses->pluck('id')->toArray());
            var classMap = {};
            @foreach($filteredClasses as $cls)
                        classMap[{{ $cls->id }}] = '{{ $cls->name }}';
            @endforeach
            var periodsByDayJson = @json($periodsByDayJson ?? [], 15, 512);
            var periodEndMap = {};
            var currentDropdown = null;

            Object.keys(periodsByDayJson).forEach(function(day) {
                periodEndMap[day] = {};
                var dayPeriods = periodsByDayJson[day];
                if (Array.isArray(dayPeriods)) {
                    dayPeriods.forEach(function(p) {
                        periodEndMap[day][p.start_time] = p.end_time;
                    });
                }
            });

            var swapUrl = '{{ url('admin/timetable/swap') }}';
            var moveUrlBase = '{{ url('admin/timetable') }}/';
            var deleteUrlBase = '{{ url('admin/timetable/cell') }}/';

            function showFeedback(message, type) {
                if (!feedbackEl) return;
                var variants = {
                    'error': { 'bg': 'bg-danger-50 dark:bg-danger-950/40', 'border': 'border-l-4 border-danger-200 dark:border-danger-800', 'text': 'text-danger-800 dark:text-danger-200', 'color': 'text-danger-600 dark:text-danger-400' },
                    'success': { 'bg': 'bg-success-50 dark:bg-success-950/40', 'border': 'border-l-4 border-success-200 dark:border-success-800', 'text': 'text-success-800 dark:text-success-200', 'color': 'text-success-600 dark:text-success-400' },
                    'info': { 'bg': 'bg-info-50 dark:bg-info-950/40', 'border': 'border-l-4 border-info-200 dark:border-info-800', 'text': 'text-info-800 dark:text-info-200', 'color': 'text-info-600 dark:text-info-400' },
                };
                var v = variants[type || 'error'] || variants['error'];
                feedbackEl.className = 'fixed top-20 left-0 right-0 z-50 mx-auto max-w-2xl rounded-xl px-4 py-3 ' + v.bg + ' ' + v.border + ' ' + v.text + ' flex items-center gap-3 shadow-md';
                feedbackEl.innerHTML = '<svg class="h-5 w-5 flex-shrink-0 ' + v.color + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="flex-1 text-sm font-semibold">' + message + '</span>';
                feedbackEl.classList.remove('hidden');
                clearTimeout(feedbackEl._hideTimer);
                feedbackEl._hideTimer = setTimeout(function() {
                    feedbackEl.classList.add('hidden');
                }, 5000);
            }

            @if(session('status'))
            showFeedback(@json(session('status')), 'success');
            @endif

            function updateCellUI(entry, day, classId, periodStart) {
                var selector = '.drop-cell[data-day="' + day + '"][data-class-id="' + classId + '"][data-period-start="' + periodStart + '"]';
                var cell = document.querySelector(selector);
                if (!cell) return;
                if (cell.querySelector('.draggable-entry')) return;

                var subjectName = '';
                var teacherName = '';
                var entryId = entry.id;
                var endTime = '';

                if (entry.class_subject) {
                    subjectName = entry.class_subject.subject ? entry.class_subject.subject.name : '';
                }
                if (entry.teacher && entry.teacher.user) {
                    teacherName = entry.teacher.user.name;
                }
                if (entry.end_time) {
                    endTime = entry.end_time.substring(0, 5);
                }

                var inner = '<div class="bg-neutral-50 timetable-entry-bg p-1 rounded text-left group relative draggable-entry" draggable="true" data-entry-id="' + entryId + '" data-current-day="' + day + '" data-current-start="' + periodStart + '" data-current-end="' + endTime + '"><div class="font-medium timetable-entry-text text-xs">' + subjectName + '</div><div class="text-xs timetable-entry-subtext">' + teacherName + '</div><div class="absolute top-0.5 right-0.5 opacity-0 group-hover:opacity-100 flex gap-1"><button type="button" class="move-entry-btn text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300" data-entry-id="' + entryId + '" data-current-day="' + day + '" data-current-start="' + periodStart + '" data-current-end="' + endTime + '" title="Move to another day/period"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 7l3-3 3 3M8 7l3 3 3-3m-9 9h12m-12 0l3-3 3 3m-3-3v6"/></svg></button><a href="/admin/timetable/' + entryId + '/edit" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-4-9l-6 6v2h2l6-6 2-2V7a2 2 0 00-2-2h-2z"/></svg></a><button type="button" class="delete-entry-btn text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300" data-entry-id="' + entryId + '" title="Delete this lesson"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.133A2 2 0 0116.138 21H7.862a2 2 0 01-1.975-1.847L5.05 7m5 5v5m4-5v5m5-10V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v3m4 0H9"/></svg></button></div></div>';
                cell.innerHTML = inner;
            }

            function closeDropdown() {
                if (currentDropdown) {
                    if (currentDropdown._updatePosition) {
                        window.removeEventListener('scroll', currentDropdown._updatePosition);
                        var scrollContainer = document.getElementById('main-content');
                        if (scrollContainer) {
                            scrollContainer.removeEventListener('scroll', currentDropdown._updatePosition);
                        }
                    }
                    currentDropdown.remove();
                    currentDropdown = null;
                }
                document.querySelectorAll('.assign-cell').forEach(function(b) {
                    b.style.pointerEvents = 'auto';
                });
            }

            function openAssignDropdown(btn) {
                btn.style.pointerEvents = 'none';
                closeDropdown();

                var day = btn.getAttribute('data-day');
                var classId = btn.getAttribute('data-class-id');
                var periodStart = btn.getAttribute('data-period-start');
                var subjects = classSubjectsByClass[classId] || [];

                if (subjects.length === 0) {
                    showFeedback('No subjects have been assigned to this class.', 'info');
                    btn.style.pointerEvents = 'auto';
                    return;
                }

                var rect = btn.getBoundingClientRect();
                var dropdown = document.createElement('div');
                dropdown.className = 'dropdown fixed z-50 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded shadow-lg overflow-y-auto w-48';
                dropdown.style.top = (rect.bottom + 4) + 'px';
                dropdown.style.left = (rect.left) + 'px';

                var header = document.createElement('div');
                header.className = 'px-2 py-1 text-xs font-medium text-neutral-500 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-700';
                header.textContent = 'Select subject for ' + classMap[classId] + ' on ' + day;
                dropdown.appendChild(header);

                subjects.forEach(function(subj) {
                    if (!subj || !subj.id) return;
                    var item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'w-full text-left px-2 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 border-b border-neutral-100 dark:border-neutral-700 last:border-0';
                    item.innerHTML = '<div class="font-medium text-xs text-neutral-900 dark:text-white">' + (subj.subject_name || '') + '</div><div class="text-xs text-neutral-500 dark:text-neutral-400">' + (subj.teacher_name || '') + '</div>';
                    item.onclick = function(e) {
                        e.stopPropagation();
                        dropdown.remove();
                        currentDropdown = null;
                        btn.style.pointerEvents = 'auto';

                        var formData = new FormData();
                        formData.append('class_subject_id', subj.id);
                        formData.append('day', day);
                        formData.append('start_time', periodStart);
                        if (subj.teacher_id) {
                            formData.append('teacher_id', subj.teacher_id);
                        }
                        formData.append('_token', csrfToken);

                        var periodEnds = periodEndMap[day] || {};
                        var endTime = periodEnds[periodStart] || '';
                        if (endTime) {
                            formData.append('end_time', endTime);
                        }

                        fetch(cellStoreUrl, {
                            method: 'POST',
                            body: formData,
                        })
                            .then(function(r) { return r.json(); })
                            .then(function(r) {
                                 if (r.success) {
                                     showFeedback('Lesson added successfully!', 'success');
                                     if (r.entry) {
                                         updateCellUI(r.entry, day, classId, periodStart);
                                     }
                                 } else {
                                     showFeedback('Could not add lesson: ' + (r.message || 'Please try again'), 'error');
                                 }
                             })
                            .catch(function(err) { showFeedback('Could not add lesson: ' + err.message, 'error'); });
                    };
                    dropdown.appendChild(item);
                });

                var divider = document.createElement('div');
                divider.className = 'border-t border-neutral-200 dark:border-neutral-700';
                dropdown.appendChild(divider);

                var assignAllItem = document.createElement('button');
                assignAllItem.type = 'button';
                assignAllItem.className = 'w-full text-left px-2 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs text-neutral-700 dark:text-neutral-300';
                assignAllItem.innerHTML = '<span class="font-medium">Assign to all classes</span><span class="text-neutral-500 dark:text-neutral-400 ml-1">(' + allClassIds.length + ')</span>';
                assignAllItem.onclick = function(e) {
                    e.stopPropagation();
                    dropdown.remove();
                    currentDropdown = null;
                    btn.style.pointerEvents = 'auto';

                    var periodEnds = periodEndMap[day] || {};
                    var endTime = periodEnds[periodStart] || '';

                    var requests = allClassIds.map(function(cid) {
                        var subjectsForClass = classSubjectsByClass[cid] || [];
                        return Promise.all(subjectsForClass.map(function(sf) {
                            var formData = new FormData();
                            formData.append('class_subject_id', sf.id);
                            formData.append('day', day);
                            formData.append('start_time', periodStart);
                            if (sf.teacher_id) {
                                formData.append('teacher_id', sf.teacher_id);
                            }
                            formData.append('_token', csrfToken);
                            if (endTime) formData.append('end_time', endTime);

                            return fetch(cellStoreUrl, { method: 'POST', body: formData })
                                .then(function(r) { return r.json(); });
                        }));
                    }).then(function(results) {
                        var allResults = results.flat();
                        var allOk = allResults.every(function(r) { return r && r.success; });
                        if (allOk) {
                            showFeedback('Lesson added to all ' + allClassIds.length + ' classes!', 'success');
                        } else {
                            showFeedback('Could not add lesson to all classes. Please check your subjects and try again.', 'error');
                        }
                    }).catch(function(err) { showFeedback('Could not add lesson: ' + err.message, 'error'); });
                };
                dropdown.appendChild(assignAllItem);

                document.body.appendChild(dropdown);
                currentDropdown = dropdown;

                var updateDropdownPosition = function() {
                    var btnRect = btn.getBoundingClientRect();
                    dropdown.style.top = (btnRect.bottom + 4) + 'px';
                    dropdown.style.left = (btnRect.left) + 'px';
                };
                dropdown._updatePosition = updateDropdownPosition;
                var scrollContainer = document.getElementById('main-content');
                if (scrollContainer) {
                    scrollContainer.addEventListener('scroll', updateDropdownPosition, { passive: true });
                }
                window.addEventListener('scroll', updateDropdownPosition, { passive: true });
            }

            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.assign-cell');
                if (!btn) {
                    var cell = e.target.closest('[data-day].drop-cell');
                    if (cell) {
                        btn = cell.querySelector('.assign-cell');
                    }
                }
                if (!btn) return;

                e.stopPropagation();
                openAssignDropdown(btn);
            });

            document.addEventListener('click', function(e) {
                if (currentDropdown && !e.target.closest('.assign-cell') && !e.target.closest('.dropdown')) {
                    closeDropdown();
                }
            });

            if (moveModal && moveForm) {
                document.querySelectorAll('.move-entry-btn').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var entryId = this.getAttribute('data-entry-id');
                        var currentDay = this.getAttribute('data-current-day');
                        var currentStart = this.getAttribute('data-current-start');
                        var currentEnd = this.getAttribute('data-current-end');

                        var formAction = moveUrlBase + entryId + '/move';
                        moveForm.action = formAction;

                        var daySelect = moveForm.querySelector('select[name="day"]');
                        var startTimeInput = moveForm.querySelector('input[name="start_time"]');
                        var endTimeInput = moveForm.querySelector('input[name="end_time"]');

                        daySelect.value = currentDay;
                        startTimeInput.value = currentStart;
                        endTimeInput.value = currentEnd;

                        moveModal.classList.remove('hidden');
                        moveModal.classList.add('flex', 'items-center', 'justify-center');
                    });
                });

                document.getElementById('move-cancel').addEventListener('click', function() {
                    moveModal.classList.remove('flex');
                    moveModal.classList.add('hidden');
                });

                moveModal.addEventListener('click', function(e) {
                    if (e.target === moveModal) {
                        moveModal.classList.remove('flex');
                        moveModal.classList.add('hidden');
                    }
                });
            }

            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.delete-entry-btn');
                if (!btn) return;

                e.stopPropagation();
                var entryId = btn.getAttribute('data-entry-id');

                var confirmBtn = document.getElementById('delete-confirm');
                var cancelBtn = document.getElementById('delete-cancel');

                var doDelete = function() {
                    deleteModal.classList.remove('flex');
                    deleteModal.classList.add('hidden');
                    cleanup();

                    fetch(deleteUrlBase + entryId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then(function(r) { return r.json(); })
                        .then(function(r) {
                            if (r.success) {
                                showFeedback('Lesson deleted.', 'success');
                                var entryEl = document.querySelector('.draggable-entry[data-entry-id="' + entryId + '"]');
                                if (entryEl && entryEl.closest('.drop-cell')) {
                                    var cellEl = entryEl.closest('.drop-cell');
                                    cellEl.innerHTML = '<button type="button" class="assign-cell text-neutral-400 hover:text-neutral-700 text-2xl font-bold" data-day="' + cellEl.getAttribute('data-day') + '" data-class-id="' + cellEl.getAttribute('data-class-id') + '" data-period-start="' + cellEl.getAttribute('data-period-start') + '">+</button>';
                                }
                            } else {
                                showFeedback('Could not delete lesson: ' + (r.message || 'Please try again'), 'error');
                            }
                        })
                        .catch(function(err) { showFeedback('Could not delete lesson: ' + err.message, 'error'); });
                };

                var cleanup = function() {
                    if (confirmBtn) confirmBtn.removeEventListener('click', doDelete);
                };

                if (confirmBtn) confirmBtn.addEventListener('click', doDelete);
                if (cancelBtn) {
                    cancelBtn.onclick = function() {
                        cleanup();
                        deleteModal.classList.remove('flex');
                        deleteModal.classList.add('hidden');
                    };
                }
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex', 'items-center', 'justify-center');
            });

            if (deleteModal) {
                deleteModal.addEventListener('click', function(e) {
                    if (e.target === deleteModal) {
                        deleteModal.classList.remove('flex');
                        deleteModal.classList.add('hidden');
                    }
                });
            }

            function doMove(entryId, toDay, toPeriodStart, toEndTime) {
                return fetch(moveUrlBase + entryId + '/move', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: 'day=' + encodeURIComponent(toDay) +
                          '&start_time=' + encodeURIComponent(toPeriodStart) +
                          '&end_time=' + encodeURIComponent(toEndTime)
                });
            }

            function doSwap(entryAId, entryBId) {
                return fetch(swapUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: 'entry_a_id=' + encodeURIComponent(entryAId) +
                          '&entry_b_id=' + encodeURIComponent(entryBId)
                });
            }

            document.addEventListener('dragstart', function(e) {
                var el = e.target.closest('.draggable-entry');
                if (!el) return;

                var entryId = el.getAttribute('data-entry-id');
                var day = el.getAttribute('data-current-day');
                var start = el.getAttribute('data-current-start');
                var cell = el.closest('[data-day]');
                var classId = cell ? cell.getAttribute('data-class-id') : null;

                e.dataTransfer.setData('text/plain', JSON.stringify({
                    entryId: entryId,
                    fromDay: day,
                    fromPeriod: start,
                    fromClassId: classId
                }));
                el.classList.add('opacity-50');
            });

            document.addEventListener('dragend', function(e) {
                var el = e.target.closest('.draggable-entry');
                if (!el) return;
                el.classList.remove('opacity-50');
            });

            var highlightClasses = ['ring-2', 'ring-primary-400', 'dark:ring-primary-500'];

            document.addEventListener('dragover', function(e) {
                var cell = e.target.closest('.drop-cell');
                if (!cell) return;
                e.preventDefault();
                highlightClasses.forEach(function(c) { cell.classList.add(c); });
            });

            document.addEventListener('dragleave', function(e) {
                var cell = e.target.closest('.drop-cell');
                if (!cell) return;
                highlightClasses.forEach(function(c) { cell.classList.remove(c); });
            });

            document.addEventListener('drop', function(e) {
                    var cell = e.target.closest('.drop-cell');
                    if (!cell) return;
                    e.preventDefault();
                    highlightClasses.forEach(function(c) { cell.classList.remove(c); });

                    var data = JSON.parse(e.dataTransfer.getData('text/plain'));
                    var toDay = cell.getAttribute('data-day');
                    var toPeriodStart = cell.getAttribute('data-period-start');

                    if (data.fromDay === toDay && data.fromPeriod === toPeriodStart) {
                        return;
                    }

                    var targetEntry = cell.querySelector('.draggable-entry');

                    if (targetEntry) {
                        var targetId = targetEntry.getAttribute('data-entry-id');

                        doSwap(data.entryId, targetId)
                            .then(function(resp) { return resp.json(); })
                            .then(function(r) {
                                if (r && r.success) {
                                    showFeedback(r.message || 'Lessons swapped successfully!', 'success');
                                    var sourceEl = document.querySelector('.draggable-entry[data-entry-id="' + data.entryId + '"]');
                                    var targetEl = document.querySelector('.draggable-entry[data-entry-id="' + targetId + '"]');
                                    if (sourceEl && targetEl && sourceEl.parentNode !== targetEl.parentNode) {
                                        var srcParent = sourceEl.parentNode;
                                        var tgtParent = targetEl.parentNode;
                                        srcParent.innerHTML = '';
                                        tgtParent.innerHTML = '';
                                        srcParent.appendChild(targetEl);
                                        tgtParent.appendChild(sourceEl);
                                        targetEl.setAttribute('data-current-day', data.fromDay);
                                        targetEl.setAttribute('data-current-start', data.fromPeriod);
                                        sourceEl.setAttribute('data-current-day', toDay);
                                        sourceEl.setAttribute('data-current-start', toPeriodStart);
                                    }
                                } else {
                                    var msg = (r && r.errors && r.errors.length) ? r.errors.join(' ') : (r && r.message ? r.message : 'Unknown error');
                                    showFeedback('Could not swap lessons: ' + msg, 'error');
                                }
                            })
                            .catch(function(err) { showFeedback('Could not swap lessons: ' + err.message, 'error'); });
                    } else {
                        var dayEndMap = periodEndMap[toDay] || periodEndMap[Object.keys(periodEndMap)[0]] || {};
                        var toEndTime = dayEndMap[toPeriodStart] || '';

                        doMove(data.entryId, toDay, toPeriodStart, toEndTime)
                            .then(function(resp) { return resp.json(); })
                            .then(function(r) {
                                if (r && r.success) {
                                    showFeedback(r.message || 'Lesson moved successfully!', 'success');
                                    var movedEl = document.querySelector('.draggable-entry[data-entry-id="' + data.entryId + '"]');
                                    var sourceCell = document.querySelector('.drop-cell[data-day="' + data.fromDay + '"][data-class-id="' + data.fromClassId + '"][data-period-start="' + data.fromPeriod + '"]');
                                    var targetCell = cell;

                                    if (movedEl && sourceCell && targetCell) {
                                        movedEl.setAttribute('data-current-day', toDay);
                                        movedEl.setAttribute('data-current-start', toPeriodStart);
                                        movedEl.setAttribute('data-current-end', r.end_time || toEndTime);
                                        sourceCell.innerHTML = '<button type="button" class="assign-cell text-neutral-400 hover:text-neutral-700 text-2xl font-bold" data-day="' + data.fromDay + '" data-class-id="' + data.fromClassId + '" data-period-start="' + data.fromPeriod + '">+</button>';
                                        targetCell.innerHTML = '';
                                        targetCell.appendChild(movedEl);
                                    }
                                } else {
                                    showFeedback('Could not move lesson: ' + (r && r.message ? r.message : 'Please try again'), 'error');
                                }
                            })
                            .catch(function(err) { showFeedback('Could not move lesson: ' + err.message, 'error'); });
                    }
                });
            })();
    </script>
    @endpush
</x-layouts.app>
