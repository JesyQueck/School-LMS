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

        {{-- D. Whole School Timetable --}}
        <x-ui.card class="p-3">
<div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Whole School Timetable</h3>
                    <button type="button" id="refresh-timetable" class="inline-flex items-center gap-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 border border-neutral-300 dark:border-dark-border rounded px-2 py-1 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        Refresh
                    </button>
                </div>
            </div>
            <div class="p-2" id="class-grids">
                @php
                    $allDays = App\Http\Controllers\Admin\TimetableController::DAYS;
                    $activePeriods = $config?->periods?->where('is_break', false)->sortBy('sort_order') ?? collect();
                    $filteredClasses = $request->input('class_id') ? $classes->where('id', $request->input('class_id')) : $classes;

                    $periodEndMap = $activePeriods->keyBy(fn($p) => \Carbon\Carbon::parse($p->start_time)->format('H:i'))
                        ->map(fn($p) => \Carbon\Carbon::parse($p->end_time)->format('H:i'))
                        ->toArray();
                @endphp

                @if($timetable->isEmpty())
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 mb-4">
                            <svg class="h-8 w-8 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 9h10m-10 4h10M8 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm8 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/></svg>
                        </div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">No timetable entries found yet. Configure your timetable structure to get started.</p>
                        <div class="flex justify-center">
                            <a href="{{ route('admin.timetable.config') }}" class="inline-flex items-center justify-center gap-2 bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0a2.34 2.34 0 0 0 3.319 1.915a2.34 2.34 0 0 1 2.33 4.033a2.34 2.34 0 0 0 0 3.831a2.34 2.34 0 0 1-2.33 4.033a2.34 2.34 0 0 0-3.319 1.915a2.34 2.34 0 0 1-4.659 0a2.34 2.34 0 0 0-3.32-1.915a2.34 2.34 0 0 1-2.33-4.033a2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915z"/><circle cx="12" cy="12" r="3"/></g></svg>
                                Configure Timetable
                            </a>
                        </div>
                    </div>
                @else
                    @php
                        $allDays = App\Http\Controllers\Admin\TimetableController::DAYS;
                        $activePeriods = $config?->periods?->where('is_break', false)->sortBy('sort_order') ?? collect();
                        $periodTimes = $periodTimes ?? [];
                        $periodEndMap = $activePeriods->keyBy(fn($p) => \Carbon\Carbon::parse($p->start_time)->format('H:i'))
                            ->map(fn($p) => \Carbon\Carbon::parse($p->end_time)->format('H:i'))
                            ->toArray();
                        $filteredClasses = $request->input('class_id') ? $classes->where('id', $request->input('class_id')) : $classes;
                        $periodLabels = [];
                        foreach ($activePeriods as $period) {
                            $periodStart = \Carbon\Carbon::parse($period->start_time)->format('H:i');
                            $periodLabels[$period->period_number] = $period->name . ' (' . \Carbon\Carbon::parse($period->start_time)->format('g:i A') . '–' . \Carbon\Carbon::parse($period->end_time)->format('g:i A') . ')';
                        }
                    @endphp>
                    @php
                        $allDays = App\Http\Controllers\Admin\TimetableController::DAYS;
                        $activePeriods = $config?->periods?->where('is_break', false)->sortBy('sort_order') ?? collect();
                        $periodTimes = $periodTimes ?? [];
                        $periodEndMap = $activePeriods->keyBy(fn($p) => \Carbon\Carbon::parse($p->start_time)->format('H:i'))
                            ->map(fn($p) => \Carbon\Carbon::parse($p->end_time)->format('H:i'))
                            ->toArray();
                        $filteredClasses = $request->input('class_id') ? $classes->where('id', $request->input('class_id')) : $classes;
                    @endphp>
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

                                <div class="class-grid-content overflow-x-auto" data-class-id="{{ $cls->id }}">
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
                                                         <td class="px-1 py-1 text-center border-r last:border-0 drop-cell"
                                                             data-day="{{ $d }}"
                                                             data-period="{{ $period->period_number }}"
                                                             data-period-start="{{ $periodStart }}">
                                                             @if($slot)
                                                                 @php $isLocked = $slot->is_locked; @endphp
                                                                 <div class="{{ $isLocked ? 'bg-warning-50 dark:bg-warning-900/20' : 'bg-neutral-50 dark:bg-neutral-800/50' }} p-1 rounded text-left group relative {{ $isLocked ? '' : 'draggable-entry' }}"
                                                                     draggable="{{ $isLocked ? 'false' : 'true' }}"
                                                                     data-entry-id="{{ $slot->id }}"
                                                                     data-current-day="{{ $slot->day }}"
                                                                      data-current-start="{{ $periodStart }}"
                                                                      data-current-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}">
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
        
    </div>

    {{-- Quick-Move Modal --}}
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

        var moveModal = document.getElementById('move-modal');
        var moveForm = document.getElementById('move-form');

        document.querySelectorAll('.move-entry-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var entryId = this.getAttribute('data-entry-id');
                var currentDay = this.getAttribute('data-current-day');
                var currentStart = this.getAttribute('data-current-start');
                var currentEnd = this.getAttribute('data-current-end');

                var formAction = '{{ url('admin/timetable') }}/' + entryId + '/move';
                moveForm.action = formAction;

                var daySelect = moveForm.querySelector('select[name="day"]');
                var startTimeInput = moveForm.querySelector('input[name="start_time"]');
                var endTimeInput = moveForm.querySelector('input[name="end_time"]');

                daySelect.value = currentDay;
                startTimeInput.value = currentStart;
                endTimeInput.value = currentEnd;

                moveModal.classList.remove('hidden');
                moveModal.classList.add('flex');
                moveModal.classList.add('items-center');
                moveModal.classList.add('justify-center');
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

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        var periodEndMap = @json($periodEndMap ?? []);
        var swapUrl = '{{ url('admin/timetable/swap') }}';
        var moveUrl = '{{ url('admin/timetable') }}/';
        var feedbackEl = document.getElementById('timetable-feedback');

        function showFeedback(message, type) {
            var variants = {
                'error': { 'border': 'border-l-4 border-danger-200 dark:border-danger-800', 'text': 'text-danger-800 dark:text-danger-200', 'bg': 'bg-danger-50 dark:bg-danger-950/40', 'color': 'text-danger-600 dark:text-danger-400' },
                'success': { 'border': 'border-l-4 border-success-200 dark:border-success-800', 'text': 'text-success-800 dark:text-success-200', 'bg': 'bg-success-50 dark:bg-success-950/40', 'color': 'text-success-600 dark:text-success-400' },
                'info': { 'border': 'border-l-4 border-info-200 dark:border-info-800', 'text': 'text-info-800 dark:text-info-200', 'bg': 'bg-info-50 dark:info-950/40', 'color': 'text-info-600 dark:text-info-400' },
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

        var refreshBtn = document.getElementById('refresh-timetable');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                window.location.reload();
            });
        }

        function doMove(entryId, toDay, toPeriodStart, toEndTime) {
            return fetch(moveUrl + entryId + '/move', {
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

        document.querySelectorAll('.draggable-entry').forEach(function(el) {
            el.addEventListener('dragstart', function(e) {
                var entryId = this.getAttribute('data-entry-id');
                var day = this.getAttribute('data-current-day');
                var start = this.getAttribute('data-current-start');

                var classGrid = this.closest('.class-grid-content');
                var gridId = classGrid ? classGrid.getAttribute('data-class-id') : null;

                e.dataTransfer.setData('text/plain', JSON.stringify({
                    entryId: entryId,
                    fromDay: day,
                    fromPeriod: start,
                    gridId: gridId
                }));
                this.classList.add('opacity-50');
            });

            el.addEventListener('dragend', function() {
                this.classList.remove('opacity-50');
            });
        });

        var dropCells = document.querySelectorAll('.drop-cell');
        var highlightClasses = ['ring-2', 'ring-primary-400', 'dark:ring-primary-500'];

        dropCells.forEach(function(cell) {
            cell.addEventListener('dragover', function(e) {
                e.preventDefault();
                cell.classList.add(...highlightClasses);
            });

            cell.addEventListener('dragleave', function() {
                cell.classList.remove(...highlightClasses);
            });

            cell.addEventListener('drop', function(e) {
                e.preventDefault();
                cell.classList.remove(...highlightClasses);

                var data = JSON.parse(e.dataTransfer.getData('text/plain'));
                var toDay = cell.getAttribute('data-day');
                var toPeriodStart = cell.getAttribute('data-period-start');
                var toEndTime = periodEndMap[toPeriodStart] || '';

                if (data.fromDay === toDay && data.fromPeriod === toPeriodStart) {
                    return;
                }

                var targetEntry = cell.querySelector('.draggable-entry');

                if (targetEntry) {
                    var targetId = targetEntry.getAttribute('data-entry-id');

                    doSwap(data.entryId, targetId)
                            .then(function(resp) {
                                return resp.json();
                            })
                            .then(function(r) {
                                if (r && r.success) {
                                    showFeedback(r.message || 'Entries swapped successfully.', 'success');
                                    var sourceEl = document.querySelector('.draggable-entry[data-entry-id="' + data.entryId + '"]');
                                    var targetEl = document.querySelector('.draggable-entry[data-entry-id="' + targetId + '"]');
                                    if (sourceEl && targetEl && sourceEl.parentNode !== targetEl.parentNode) {
                                        var srcParent = sourceEl.parentNode;
                                        var tgtParent = targetEl.parentNode;
                                        srcParent.innerHTML = '';
                                        tgtParent.innerHTML = '';
                                        srcParent.appendChild(targetEl.cloneNode(true));
                                        tgtParent.appendChild(sourceEl.cloneNode(true));
                                    }
                                } else {
                                    var msg = (r && r.errors && r.errors.length) ? r.errors.join(' ') : (r && r.message ? r.message : 'Unknown error');
                                    showFeedback('Swap failed: ' + msg, 'error');
                                }
                            })
                            .catch(function(err) { showFeedback('Swap error: ' + err.message, 'error'); });
                } else {
                    doMove(data.entryId, toDay, toPeriodStart, toEndTime)
                        .then(function(resp) {
                            return resp.json();
                        })
                        .then(function(r) {
                            if (r && r.success) {
                                showFeedback(r.message || 'Entry moved successfully.', 'success');

                                var movedEl = document.querySelector('.draggable-entry[data-entry-id="' + data.entryId + '"]');
                                var sourceGrid = data.gridId ? document.querySelector('.class-grid-content[data-class-id="' + data.gridId + '"]') : null;
                                var sourceCell = sourceGrid ? sourceGrid.querySelector('.drop-cell[data-day="' + data.fromDay + '"][data-period-start="' + data.fromPeriod + '"]') : document.querySelector('.drop-cell[data-day="' + data.fromDay + '"][data-period-start="' + data.fromPeriod + '"]');

                                var targetCell = cell;

                                if (movedEl && sourceCell && targetCell) {
                                    movedEl.setAttribute('data-current-day', toDay);
                                    movedEl.setAttribute('data-current-start', toPeriodStart);
                                    movedEl.setAttribute('data-current-end', r.end_time || toEndTime);

                                    sourceCell.innerHTML = '<span class="text-neutral-300 dark:text-neutral-600">—</span>';
                                    targetCell.innerHTML = '';
                                    targetCell.appendChild(movedEl);
                                }
                            } else {
                                showFeedback('Move failed: ' + (r && r.message ? r.message : 'Unknown error'), 'error');
                            }
                        })
                        .catch(function(err) { showFeedback('Error: ' + err.message, 'error'); });
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
                div.className = 'grid grid-cols-1 gap-1.5 py-1';
                div.innerHTML = `
                    <div>
                        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-0.5">Name</label>
                        <input type="text" name="periods[${periodCounter}][name]" placeholder="e.g. Period ${periodCounter}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-1.5 py-0.5 text-xs">
                    </div>
                    <input type="hidden" name="periods[${periodCounter}][period_number]" value="${periodCounter}">
                    <input type="hidden" name="periods[${periodCounter}][sort_order]" value="${periodCounter}">
                    <input type="hidden" name="periods[${periodCounter}][is_break]" value="0">
                    <div class="grid grid-cols-3 gap-1.5 items-end">
                        <div>
                            <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-0.5">Start</label>
                            <input type="time" name="periods[${periodCounter}][start_time]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-1.5 py-0.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-0.5">End</label>
                            <input type="time" name="periods[${periodCounter}][end_time]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-1.5 py-0.5 text-xs">
                        </div>
                        <div class="flex items-end pb-0.5">
                            <label class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                                <input type="checkbox" name="periods[${periodCounter}][is_break]" value="1" class="rounded border-neutral-300 dark:border-dark-border">
                                Break
                            </label>
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
        }
    </script>
    @endpush
</x-layouts.app>
