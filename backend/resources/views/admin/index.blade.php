<x-layouts.app title="Admin Dashboard">
    @php
        $trendPayload = $attendance
            ? json_encode($attendance['trend'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR)
            : '{}';
        $comparisonPayload = $attendance
            ? json_encode($attendance['classComparison'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR)
            : '{}';
        $enrollmentPayload = json_encode(['rows' => $enrollment], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
        $detailClass = $classes->firstWhere('id', $selectedClassId);
        $dateMax = $currentTerm?->end_date && $currentTerm->end_date->lessThan(now()->startOfDay())
            ? $currentTerm->end_date->toDateString()
            : now()->toDateString();
        $todayAttendance = $attendance['today'] ?? null;
        $todayCoverageValue = ! $currentTerm || ! $todayAttendance['available']
            ? 'Unavailable'
            : ($todayAttendance['roster'] === 0
                ? 'No eligible roster'
                : ($todayAttendance['covered'] === 0
                    ? 'No records'
                    : ($todayAttendance['coverage'] !== null ? $todayAttendance['coverage'].'%' : 'Unavailable')));
        $todayCoverageSupport = ! $currentTerm || ! $todayAttendance['available']
            ? 'Unavailable outside the current term.'
            : ($todayAttendance['roster'] === 0
                ? 'No eligible active roster.'
                : number_format($todayAttendance['covered']).' of '.number_format($todayAttendance['roster']).' students recorded; completion, not presence.');
    @endphp

    <div class="mx-auto w-full max-w-[1600px] space-y-7">
        <header class="flex flex-col gap-4 border-b border-neutral-200 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-dark-border">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary-800 dark:text-primary-300">Greenfield Academy · School overview</p>
                <h1 class="mt-1.5 text-2xl font-bold tracking-tight text-neutral-950 dark:text-white sm:text-3xl">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="mt-1.5 text-sm text-neutral-600 dark:text-neutral-400">
                    {{ now()->format('l, j F Y') }}
                    @if($currentTerm)
                        <span aria-hidden="true">·</span> {{ $currentTerm->academicSession?->name ?? 'Academic session unavailable' }} · {{ $currentTerm->name }}
                    @else
                        <span aria-hidden="true">·</span> Current term is not configured
                    @endif
                </p>
            </div>
            <nav aria-label="Admin quick actions" class="flex flex-wrap gap-2">
                <a href="{{ route('admin.students') }}" class="inline-flex min-h-10 items-center rounded-lg bg-primary-700 px-4 text-sm font-semibold text-white transition hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Manage students</a>
                <a href="{{ route('admin.results') }}" class="inline-flex min-h-10 items-center rounded-lg border border-neutral-200 bg-white px-4 text-sm font-semibold text-neutral-700 transition hover:border-primary-400 hover:text-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:border-dark-border dark:bg-dark-surface dark:text-neutral-200 dark:hover:text-white">Results</a>
                <a href="{{ route('admin.finance') }}" class="inline-flex min-h-10 items-center rounded-lg border border-neutral-200 bg-white px-4 text-sm font-semibold text-neutral-700 transition hover:border-primary-400 hover:text-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:border-dark-border dark:bg-dark-surface dark:text-neutral-200 dark:hover:text-white">Fees</a>
            </nav>
        </header>

        @if(session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        @if(($attendance['syntheticCount'] ?? 0) > 0)
            <div role="status" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
                Synthetic records included: {{ number_format($attendance['syntheticCount']) }} · simulated, not genuine.
            </div>
        @endif

        <section aria-label="School key metrics" class="grid grid-cols-1 gap-3 sm:grid-cols-2 2xl:grid-cols-4">
            <x-ui.stat-card refined label="Active students" :value="number_format($activeStudents)" icon="users" />
            <x-ui.stat-card refined label="Active teachers" :value="number_format($activeTeachers)" icon="graduation-cap" />
            <x-ui.stat-card refined label="Classes" :value="number_format($totalClasses)" icon="layout-grid" />
            <x-ui.stat-card refined label="Today's register completion" :value="$todayCoverageValue" :supporting-text="$todayCoverageSupport" icon="calendar-check" />
        </section>

        <section aria-labelledby="attendance-heading" class="space-y-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="attendance-heading" class="text-lg font-bold text-neutral-950 dark:text-white">Attendance analytics</h2>
                    <p class="text-sm leading-6 text-neutral-600 dark:text-neutral-400">
                        @if($currentTerm)
                            Whole-school · {{ $currentTerm->name }}
                        @else
                            Current term not configured.
                        @endif
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="grid min-w-0 grid-cols-1 gap-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-dark-border dark:bg-dark-surface sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                <div>
                    <label for="attendance-class" class="mb-1 block text-sm font-medium text-neutral-800 dark:text-neutral-200">Class</label>
                    <select id="attendance-class" name="class_id" class="min-h-11 w-full rounded-lg border border-neutral-300 bg-white px-3 text-sm text-neutral-900 focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600/20 dark:border-dark-border dark:bg-dark-bg dark:text-white" {{ $currentTerm ? '' : 'disabled' }}>
                        <option value="">Choose a class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected($selectedClassId === $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="attendance-date" class="mb-1 block text-sm font-medium text-neutral-800 dark:text-neutral-200">Date</label>
                    <input
                        id="attendance-date"
                        name="date"
                        type="date"
                        value="{{ $selectedDate?->toDateString() }}"
                        min="{{ $currentTerm?->start_date?->toDateString() }}"
                        max="{{ $dateMax }}"
                        class="min-h-11 w-full rounded-lg border border-neutral-300 bg-white px-3 text-sm text-neutral-900 focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600/20 dark:border-dark-border dark:bg-dark-bg dark:text-white"
                        {{ $currentTerm ? '' : 'disabled' }}
                    >
                </div>
                <button type="submit" class="min-h-11 rounded-lg bg-neutral-900 px-5 text-sm font-semibold text-white transition hover:bg-neutral-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:bg-primary-700 dark:hover:bg-primary-600" {{ $currentTerm ? '' : 'disabled' }}>Apply</button>
                @if($currentTerm)
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:col-span-3">Comparison is school-wide for this date; class selection filters detail only.</p>
                @endif
            </form>

            @if($currentTerm)
                <div class="grid min-w-0 grid-cols-1 gap-4 xl:grid-cols-2">
                    <section aria-labelledby="term-trend-title">
                        @if(count($attendance['trend']['labels']) > 0)
                            <x-ui.chart class="h-full" title="Whole-school attendance trend" description="Recorded entries by date and status · current term." :height="max(250, min(340, count($attendance['trend']['labels']) * 7))">
                                <canvas data-dashboard-chart="attendance" data-chart-data="{{ $trendPayload }}" role="img" aria-label="Whole-school current-term register entry counts by date and status"></canvas>
                            </x-ui.chart>
                            <table class="sr-only">
                                <caption id="term-trend-title">Whole-school current-term attendance entries by date and status</caption>
                                <thead>
                                    <tr><th scope="col">Date</th>@foreach($attendance['trend']['datasets'] as $dataset)<th scope="col">{{ $dataset['label'] }} records</th>@endforeach</tr>
                                </thead>
                                <tbody>
                                    @foreach($attendance['trend']['rows'] as $row)
                                        <tr><th scope="row">{{ $row['label'] }}</th>@foreach($attendance['trend']['datasets'] as $dataset)<td>{{ $row[$dataset['label']] ?? 0 }}</td>@endforeach</tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <x-ui.card>
                                <h3 id="term-trend-title" class="text-base font-semibold text-neutral-950 dark:text-white">Whole-school attendance trend</h3>
                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">No records for {{ $currentTerm->name }}.</p>
                            </x-ui.card>
                        @endif
                    </section>

                    <section aria-labelledby="class-comparison-title">
                        @if($selectedDate && $attendance['classComparison']['hasRecords'])
                            <x-ui.chart class="h-full" title="Class comparison · {{ $selectedDate->format('j M Y') }}" description="Recorded counts by status, not attendance rates." :height="max(250, min(360, count($classes) * 42))">
                                <canvas data-dashboard-chart="attendance-classes" data-chart-data="{{ $comparisonPayload }}" role="img" aria-label="Class-by-class attendance record counts by status on {{ $selectedDate->format('j M Y') }}"></canvas>
                            </x-ui.chart>
                            <table class="sr-only">
                                <caption id="class-comparison-title">Attendance entries by class and status on {{ $selectedDate->format('j M Y') }}</caption>
                                <thead><tr><th scope="col">Class</th>@foreach($attendance['classComparison']['datasets'] as $dataset)<th scope="col">{{ $dataset['label'] }} records</th>@endforeach<th scope="col">Register state</th></tr></thead>
                                <tbody>
                                    @foreach($attendance['classComparison']['rows'] as $row)
                                        <tr><th scope="row">{{ $row['label'] }}</th>@foreach($attendance['classComparison']['datasets'] as $dataset)<td>{{ $row[$dataset['label']] ?? 0 }}</td>@endforeach<td>{{ $row['hasRecords'] ? 'Records available' : 'No records' }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @php($classesWithoutRecords = collect($attendance['classComparison']['rows'])->where('hasRecords', false)->pluck('label'))
                            @if($classesWithoutRecords->isNotEmpty())
                                <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400">No records: {{ $classesWithoutRecords->join(', ') }}.</p>
                            @endif
                        @else
                            <x-ui.card>
                                <h3 id="class-comparison-title" class="text-base font-semibold text-neutral-950 dark:text-white">Class comparison</h3>
                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $selectedDate ? 'No records for '.$selectedDate->format('j M Y').'.' : 'Select a date to compare classes.' }}</p>
                            </x-ui.card>
                        @endif
                    </section>
                </div>

                @if($detailClass && $selectedDate)
                <section aria-labelledby="class-detail-title">
                    <x-ui.card class="border-l-4 border-l-primary-700 dark:border-l-primary-400">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 id="class-detail-title" class="text-base font-semibold text-neutral-950 dark:text-white">
                                    {{ $detailClass ? $detailClass->name.' register detail' : 'Class register detail' }}
                                </h3>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $selectedDate->format('j F Y') }} · {{ $currentTerm->name }}</p>
                            </div>
                            @if($detailClass && $selectedDate && $attendance['classDetail']['coverageAvailable'])
                                <p class="text-sm font-semibold text-primary-800 dark:text-primary-200">
                                    Register completion {{ $attendance['classDetail']['coverage'] }}%
                                    <span class="font-normal text-neutral-600 dark:text-neutral-400">({{ $attendance['classDetail']['covered'] }}/{{ $attendance['classDetail']['roster'] }} students recorded; not attendance rate)</span>
                                </p>
                            @elseif($detailClass && $selectedDate && $selectedDate->isToday())
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $attendance['classDetail']['hasRecords'] ? 'Coverage unavailable: no matching active-roster records.' : 'No records for this class and date.' }}
                                </p>
                            @elseif($detailClass && $selectedDate)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Historical coverage unavailable; past roster unknown.</p>
                            @endif
                        </div>

                        @if($detailClass && $selectedDate)
                            @if($attendance['classDetail']['hasRecords'])
                                <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    @foreach($attendance['classDetail']['rows'] as $row)
                                        <div class="rounded-lg bg-neutral-50 p-3 dark:bg-neutral-800/60">
                                            <dt class="text-xs font-medium text-neutral-600 dark:text-neutral-400">{{ $row['label'] }} records</dt>
                                            <dd class="mt-1 text-xl font-semibold text-neutral-950 dark:text-white">{{ number_format($row['count']) }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="mt-4 rounded-lg bg-neutral-50 px-4 py-3 text-sm text-neutral-600 dark:bg-neutral-800/60 dark:text-neutral-400">No records. Missing entries are not absences.</p>
                            @endif
                        @endif
                    </x-ui.card>
                </section>
                @endif
            @else
                <x-ui.card>
                    <h3 class="text-base font-semibold text-neutral-950 dark:text-white">Attendance analytics unavailable</h3>
                    <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Configure a current term to view attendance.</p>
                    <a href="{{ route('admin.academic') }}" class="mt-3 inline-flex text-sm font-semibold text-primary-700 hover:underline dark:text-primary-300">Open academic setup</a>
                </x-ui.card>
            @endif
        </section>

        <section aria-label="Enrollment and finance overview" class="grid min-w-0 grid-cols-1 items-stretch gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <div class="min-w-0">
                @if(count($enrollment) > 0)
                    <x-ui.chart class="h-full" title="Active enrollment by class" description="Active students by current class." :height="max(250, count($enrollment) * 48)">
                        <canvas data-dashboard-chart="enrollment" data-chart-data="{{ $enrollmentPayload }}" role="img" aria-label="Active student enrollment by current class"></canvas>
                    </x-ui.chart>
                    <table class="sr-only">
                        <caption>Active student enrollment by current class</caption>
                        <thead><tr><th scope="col">Class</th><th scope="col">Active students</th></tr></thead>
                        <tbody>@foreach($enrollment as $row)<tr><th scope="row">{{ $row['label'] }}</th><td>{{ $row['count'] }}</td></tr>@endforeach</tbody>
                    </table>
                @else
                    <x-ui.card class="h-full"><h3 class="text-base font-semibold text-neutral-950 dark:text-white">Active enrollment</h3><p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">No classes.</p></x-ui.card>
                @endif
            </div>

            <section class="min-w-0" aria-labelledby="finance-title">
                <x-ui.card :padding="false" class="h-full">
                    <div class="h-full p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-800 ring-1 ring-primary-100 dark:bg-primary-900/30 dark:text-primary-200 dark:ring-primary-800/50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5A2.5 2.5 0 015.5 5H20v14H5.5A2.5 2.5 0 013 16.5v-9zM3 8h17m-5 6h2"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 id="finance-title" class="text-sm font-semibold text-neutral-950 dark:text-white">Finance Overview</h3>
                                <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-neutral-400">All recorded terms.</p>
                            </div>
                        </div>
                        <dl class="mt-4 divide-y divide-neutral-100 border-y border-neutral-100 dark:divide-dark-border dark:border-dark-border">
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <dt class="text-xs font-medium text-neutral-600 dark:text-neutral-400">Collected</dt>
                                <dd class="text-right text-sm font-semibold tabular-nums text-neutral-950 dark:text-white">₦{{ number_format($finance['collected'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <dt class="text-xs font-medium text-neutral-600 dark:text-neutral-400">Outstanding</dt>
                                <dd class="text-right text-sm font-semibold tabular-nums text-neutral-950 dark:text-white">₦{{ number_format($finance['outstanding'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <dt class="text-xs font-medium text-neutral-600 dark:text-neutral-400">Expected fees</dt>
                                <dd class="text-right text-sm font-semibold tabular-nums text-neutral-950 dark:text-white">₦{{ number_format($finance['expected'], 2) }}</dd>
                            </div>
                        </dl>
                        <a href="{{ route('admin.finance') }}" class="mt-3 inline-flex text-xs font-semibold text-primary-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-primary-300">Open finance</a>
                    </div>
                </x-ui.card>
            </section>
        </section>

        <section aria-label="Results and school updates" class="grid grid-cols-1 items-stretch gap-4 md:grid-cols-2 xl:grid-cols-3">
            <section class="min-w-0" aria-labelledby="results-title">
                <x-ui.card :padding="false" class="h-full">
                    <div class="flex h-full flex-col p-4 sm:p-5">
                        <div class="flex min-h-10 items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 id="results-title" class="text-sm font-semibold text-neutral-950 dark:text-white">Result Records</h3>
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ $currentTerm ? $currentTerm->name : 'Current term unavailable' }}</p>
                            </div>
                            <a href="{{ route('admin.results') }}" class="shrink-0 text-xs font-semibold text-primary-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-primary-300">Manage</a>
                        </div>
                        @if($resultMetrics)
                            <dl class="mt-4 grid grid-cols-3 divide-x divide-neutral-200 rounded-lg border border-neutral-200 dark:divide-dark-border dark:border-dark-border">
                                <div class="px-2 py-2 text-center"><dt class="text-[11px] text-neutral-600 dark:text-neutral-400">Recorded</dt><dd class="mt-0.5 text-base font-semibold text-neutral-950 dark:text-white">{{ number_format($resultMetrics['recorded']) }}</dd></div>
                                <div class="px-2 py-2 text-center"><dt class="text-[11px] text-neutral-600 dark:text-neutral-400">Locked</dt><dd class="mt-0.5 text-base font-semibold text-neutral-950 dark:text-white">{{ number_format($resultMetrics['locked']) }}</dd></div>
                                <div class="px-2 py-2 text-center"><dt class="text-[11px] text-neutral-600 dark:text-neutral-400">Unlocked</dt><dd class="mt-0.5 text-base font-semibold text-neutral-950 dark:text-white">{{ number_format($resultMetrics['unlocked']) }}</dd></div>
                            </dl>
                        @else
                            <p class="mt-3 text-xs text-neutral-600 dark:text-neutral-400">No current term.</p>
                        @endif
                    </div>
                </x-ui.card>
            </section>

            <section class="min-w-0" aria-labelledby="announcements-title">
                <x-ui.card :padding="false" class="h-full">
                    <div class="flex h-full flex-col p-4 sm:p-5">
                        <div class="flex min-h-10 items-start justify-between gap-3">
                            <h3 id="announcements-title" class="text-sm font-semibold text-neutral-950 dark:text-white">Recent Announcements</h3>
                            <a href="{{ route('admin.announcements') }}" class="shrink-0 text-xs font-semibold text-primary-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-primary-300">All</a>
                        </div>
                        @forelse($recentAnnouncements->take(3) as $announcement)
                            <div class="flex items-start justify-between gap-3 border-t border-neutral-100 py-2 first:mt-1 dark:border-dark-border">
                                <p class="line-clamp-2 min-w-0 break-words text-xs font-medium leading-5 text-neutral-900 dark:text-white">{{ $announcement->title }}</p>
                                <time class="shrink-0 pt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400" datetime="{{ $announcement->created_at->toDateString() }}">{{ $announcement->created_at->format('j M Y') }}</time>
                            </div>
                        @empty
                            <div class="mt-2 border-t border-neutral-100 pt-3 dark:border-dark-border">
                                <p class="text-xs text-neutral-600 dark:text-neutral-400">No published announcements.</p>
                                <a href="{{ route('admin.announcements.create') }}" class="mt-2 inline-flex text-xs font-semibold text-primary-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-primary-300">Create announcement</a>
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </section>

            <section class="min-w-0" aria-labelledby="activity-title">
                <x-ui.card :padding="false" class="h-full">
                    <div class="flex h-full flex-col p-4 sm:p-5">
                        <div class="flex min-h-10 items-start justify-between gap-3">
                            <h3 id="activity-title" class="text-sm font-semibold text-neutral-950 dark:text-white">Recent Activity</h3>
                            <a href="{{ route('admin.audit-logs.index') }}" class="shrink-0 text-xs font-semibold text-primary-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-primary-300">Audit log</a>
                        </div>
                        @forelse($recentActivity->take(3) as $activity)
                            <div class="border-t border-neutral-100 py-2 first:mt-1 dark:border-dark-border">
                                <p class="line-clamp-2 break-words text-xs font-medium leading-5 text-neutral-900 dark:text-white">{{ $auditPresentation[$activity->id]['action'] }}</p>
                                <p class="mt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400">{{ $auditPresentation[$activity->id]['actor'] }} · <time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->diffForHumans() }}</time></p>
                            </div>
                        @empty
                            <p class="mt-2 border-t border-neutral-100 pt-3 text-xs text-neutral-600 dark:border-dark-border dark:text-neutral-400">No recent activity recorded.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </section>
        </section>

        <nav aria-label="More admin sections" class="flex flex-wrap gap-x-5 gap-y-2 border-t border-neutral-200 pt-4 text-sm dark:border-dark-border">
            <a href="{{ route('admin.teachers') }}" class="font-medium text-neutral-600 hover:text-primary-700 dark:text-neutral-400 dark:hover:text-primary-300">Teacher management</a>
            <a href="{{ route('admin.classes') }}" class="font-medium text-neutral-600 hover:text-primary-700 dark:text-neutral-400 dark:hover:text-primary-300">Classes</a>
            <a href="{{ route('admin.academic') }}" class="font-medium text-neutral-600 hover:text-primary-700 dark:text-neutral-400 dark:hover:text-primary-300">Academic setup</a>
        </nav>
    </div>
</x-layouts.app>
