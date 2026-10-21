<x-layouts.app title="Parent Dashboard">
    @php
        $user = auth()->user();
        $greeting = 'Good ' . (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening'));
        $lastName = $user->name ? trim(collect(explode(' ', $user->name))->last()) : '';
        $session = $currentTerm && $currentTerm->academicSession
            ? $currentTerm->academicSession->name
            : 'Current Session';
        $termName = $currentTerm ? $currentTerm->name : 'Current Term';
        $resultTrendPayload = json_encode($resultTrend, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $greeting }}, {{ $lastName }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Choose a linked child to review their published results and school summaries.</p>
        <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-2">{{ $session }} &middot; {{ $termName }}</p>
    </div>

    @if($stats['children'] > 0)
        <p class="mb-5 rounded-lg bg-primary-50 px-4 py-3 text-xs text-primary-900 dark:bg-primary-900/20 dark:text-primary-200">
            Household overview · {{ $stats['children'] }} linked {{ $stats['children'] === 1 ? 'child' : 'children' }} ·
            attendance average {{ $stats['attendance'] !== null ? $stats['attendance'].'%' : 'no records' }}
            ({{ $stats['attendance_children'] }}/{{ $stats['children'] }} with records) ·
            published-result average {{ $stats['avg_result'] !== null ? $stats['avg_result'].'%' : 'no results' }}
            ({{ $stats['result_children'] }}/{{ $stats['children'] }} with published results) ·
            fees due ₦{{ number_format($stats['fees_due'], 2) }} across all terms.
        </p>
    @endif

    <div class="space-y-8">
        {{-- MY CHILDREN --}}
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 id="my-children" class="text-base font-semibold text-neutral-900 dark:text-white">Linked children</h2>
                @if($children->isNotEmpty())
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Select a child to switch dashboard context</span>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @forelse($children as $child)
                    <x-ui.card>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-semibold flex-shrink-0">
                                {{ substr($child->full_name ?? $child->admission_no, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-neutral-900 dark:text-white">{{ $child->full_name ?? 'Unknown' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $child->schoolClass->name ?? 'Unassigned' }} · {{ $child->admission_no ?? 'N/A' }}</p>
                            </div>
                            @if($selectedChild?->id === $child->id)
                                <span class="rounded-full bg-primary-100 px-2 py-1 text-[11px] font-medium text-primary-800 dark:bg-primary-900/40 dark:text-primary-200">Selected</span>
                            @else
                                <a href="{{ route('parent.dashboard', ['student' => $child->id]) }}" class="text-xs font-medium text-primary-700 hover:underline dark:text-primary-300" aria-label="View {{ $child->full_name ?? 'child' }} dashboard">View</a>
                            @endif
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('parent.children.show', $child) }}" class="text-xs font-medium text-neutral-600 hover:text-primary-700 dark:text-neutral-400 dark:hover:text-primary-300">Profile</a>
                            <a href="{{ route('parent.children.results', $child) }}" class="text-xs font-medium text-neutral-600 hover:text-primary-700 dark:text-neutral-400 dark:hover:text-primary-300">Results</a>
                        </div>
                    </x-ui.card>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-ui.empty-state title="No children linked" description="Contact the school administrator to link students to your account." />
                    </div>
                @endforelse
            </div>
        </section>

        @if($selectedChild)
            <section aria-labelledby="child-summary-heading">
                <div class="mb-4 flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-dark-border dark:bg-dark-surface sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 id="child-summary-heading" class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $selectedChild->full_name }} <span class="text-sm font-normal text-neutral-500 dark:text-neutral-400">· {{ $selectedChild->schoolClass->name ?? 'Unassigned' }}</span></h2>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Showing summaries for this linked child only.</p>
                    </div>
                    <form method="GET" action="{{ route('parent.dashboard') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-72 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <label for="dashboard-child" class="mb-1 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Dashboard child</label>
                            <select id="dashboard-child" name="student" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text">
                                @foreach($children as $child)
                                    <option value="{{ $child->id }}" {{ $selectedChild->id === $child->id ? 'selected' : '' }}>
                                        {{ $child->full_name ?? $child->admission_no }} — {{ $child->admission_no }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-700 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                            Switch child
                        </button>
                    </form>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <x-ui.stat-card label="Published average · all terms" :value="$selectedChild->metrics['average_score'] !== null ? $selectedChild->metrics['average_score'].'%' : 'No results'" icon="clipboard-list" compact />
                <x-ui.stat-card label="Attendance · all records" :value="$selectedChild->metrics['attendance_rate'] !== null ? $selectedChild->metrics['attendance_rate'].'%' : 'No records'" icon="calendar-check" compact />
                <x-ui.stat-card label="Fees due · all terms" :value="'₦'.number_format($selectedChild->metrics['outstanding'], 2)" icon="wallet" compact />
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <section>
                    @if(count($resultTrend['rows']) > 0)
                        <x-ui.chart
                            title="Published average by term"
                            :description="count($resultTrend['rows']) === 1 ? 'One published term is available; a trend cannot be inferred.' : 'Average score for the most recent four published terms, ordered by academic chronology.'"
                            height="240"
                        >
                            <canvas
                                data-dashboard-chart="performance"
                                data-chart-data="{{ $resultTrendPayload }}"
                                role="img"
                                aria-label="Published average scores by term for {{ $selectedChild->full_name }}"
                            ></canvas>
                        </x-ui.chart>
                        <table class="sr-only">
                            <caption>Published average score by term for {{ $selectedChild->full_name }}</caption>
                            <thead><tr><th scope="col">Term</th><th scope="col">Average score</th></tr></thead>
                            <tbody>
                                @foreach($resultTrend['rows'] as $row)
                                    <tr><th scope="row">{{ $row['label'] }}</th><td>{{ $row['average'] }}%</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <x-ui.card>
                            <h3 id="child-performance-heading" class="text-base font-semibold text-neutral-900 dark:text-white">Published performance</h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">No published results are available for this child yet.</p>
                        </x-ui.card>
                    @endif
                </section>

                <section aria-labelledby="recent-results-heading">
                    <x-ui.card>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 id="recent-results-heading" class="text-base font-semibold text-neutral-900 dark:text-white">Recent results</h3>
                            <a href="{{ route('parent.children.results', $selectedChild) }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">All results</a>
                        </div>
                        <div class="divide-y divide-neutral-100 dark:divide-dark-border">
                            @forelse($recentResults as $result)
                                <div class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-neutral-800 dark:text-neutral-200">{{ $result->classSubject->subject->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $result->term->name ?? 'Term' }}</p>
                                    </div>
                                    <span class="shrink-0 font-semibold text-neutral-900 dark:text-white">{{ $result->total ?? 'N/A' }}%</span>
                                </div>
                            @empty
                                <p class="py-3 text-sm text-neutral-500 dark:text-neutral-400">No published results yet.</p>
                            @endforelse
                        </div>
                    </x-ui.card>
                </section>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <x-ui.card>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Attendance</h3>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $selectedChild->metrics['attendance_rate'] !== null ? $selectedChild->metrics['present'].' present of '.$selectedChild->metrics['total_records'].' recorded days · all records' : 'No attendance records available.' }}
                            </p>
                        </div>
                        <a href="{{ route('parent.children.attendance', $selectedChild) }}" class="shrink-0 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Details</a>
                    </div>
                </x-ui.card>
                <x-ui.card>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Fees</h3>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Outstanding balance across all recorded terms.</p>
                        </div>
                        <a href="{{ route('parent.children.fees', $selectedChild) }}" class="shrink-0 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Details</a>
                    </div>
                </x-ui.card>
            </div>
        @else
            <x-ui.empty-state title="No children linked" description="Contact the school administrator to link a child to your account. Their published results, attendance, and fee summaries will appear here." />
        @endif

        {{-- ANNOUNCEMENTS --}}
        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h2>
                <a href="{{ route('parent.announcements') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
            </div>
            @if($announcements->isNotEmpty())
                <x-ui.card :padding="false">
                    <ul class="divide-y divide-neutral-100 px-4 dark:divide-dark-border">
                        @foreach($announcements as $announcement)
                            <li class="py-3">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $announcement->title }}</p>
                                <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">{{ $announcement->created_at->format('F j, Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @else
                    <x-ui.empty-state title="No announcements" description="There are no announcements for parents at this time." />
            @endif
        </section>

        {{-- RECENT REPORT CARDS --}}
        @if($children->count() > 0)
            <section>
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-3">Recent Report Cards</h2>
                @if($recentReportCards->isNotEmpty())
                    <x-ui.card :padding="false">
                        <ul class="divide-y divide-neutral-100 px-4 dark:divide-dark-border">
                            @foreach($recentReportCards as $entry)
                                @php($child = $entry['student'])
                                @php($published = $entry['reportCard'])
                                <li class="flex items-center justify-between gap-3 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-neutral-900 dark:text-white">{{ $published->term->name ?? 'Term' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $child->full_name }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3">
                                        <span class="rounded-full bg-success-100 px-2 py-1 text-[11px] font-medium text-success-700 dark:bg-success-900/30 dark:text-success-300">Published</span>
                                        <a href="{{ route('parent.children.report-cards.download', [$child->id, $published->id]) }}" class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200" aria-label="Download {{ $child->full_name }}'s {{ $published->term->name ?? 'term' }} report card">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 19V5m0 0L7 10m5-5l5 5"/></svg>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @else
                    <x-ui.empty-state title="No published report cards" description="Report cards will appear here after the school publishes them." />
                @endif
            </section>
        @endif
    </div>
</x-layouts.app>
