<x-layouts.app title="Parent Dashboard">
    @php
        $user = auth()->user();
        $greeting = 'Good ' . (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening'));
        $lastName = $user->name ? trim(collect(explode(' ', $user->name))->last()) : '';
        $session = $currentTerm && $currentTerm->academicSession
            ? $currentTerm->academicSession->name
            : 'Current Session';
        $termName = $currentTerm ? $currentTerm->name : 'Current Term';
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $greeting }}, {{ $lastName }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Here's an overview of your children's academic progress.</p>
        <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-2">{{ $session }} &middot; {{ $termName }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Children" :value="$stats['children']" icon="users" />
        <x-ui.stat-card label="Attendance" :value="$stats['attendance'] . '%'" icon="calendar-check" />
        <x-ui.stat-card label="Avg. Result" :value="$stats['avg_result'] . '%'" icon="clipboard-list" />
        <x-ui.stat-card label="Fees Due" :value="'₦' . number_format($stats['fees_due'], 2)" icon="wallet" />
    </div>

    <div class="space-y-8">
        {{-- MY CHILDREN --}}
        <section>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-3">My Children</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($children as $child)
                    <x-ui.card hoverable>
                        <div class="flex items-start gap-4">
                            <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-lg flex-shrink-0">
                                {{ substr($child->full_name ?? $child->admission_no, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate">{{ $child->full_name ?? 'Unknown' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $child->class->name ?? 'Unassigned' }}</p>
                                <p class="text-xs text-neutral-400 dark:text-neutral-500">Admission No: {{ $child->admission_no ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Average</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $child->metrics['average_score'] }}%</p>
                            </div>
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Attendance</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $child->metrics['attendance_rate'] }}%</p>
                            </div>
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Fees</p>
                                <p class="text-sm font-semibold {{ $child->metrics['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                    {{ $child->metrics['outstanding'] > 0 ? '₦' . number_format($child->metrics['outstanding'], 0) . ' due' : 'Paid' }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('parent.children.show', $child) }}" class="flex-1 text-center text-xs font-medium px-3 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">View Profile</a>
                            <a href="{{ route('parent.children.results', $child) }}" class="flex-1 text-center text-xs font-medium px-3 py-2 rounded-lg border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">View Results</a>
                        </div>
                    </x-ui.card>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-ui.empty-state title="No children linked" description="Contact the school administrator to link students to your account." />
                    </div>
                @endforelse
            </div>
        </section>

        @if($children->count() > 0)
            @php $first = $children->first(); @endphp

            {{-- RECENT RESULTS --}}
            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Recent Results</h2>
                    <a href="{{ route('parent.children.results', $first) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Results</a>
                </div>
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-neutral-100 dark:border-dark-border flex items-center justify-between">
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $first->full_name }} &middot; {{ $first->class->name ?? '' }}</p>
                    </div>
                    <div class="p-6 space-y-2">
                        @php
                            $publishedTermIds = $first->reportCards()->where('is_published', true)->pluck('term_id');
                            $recentResults = $first->results->whereIn('term_id', $publishedTermIds)->take(5);
                        @endphp
                        @forelse($recentResults as $result)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-neutral-700 dark:text-neutral-300">{{ $result->classSubject->subject->name ?? 'N/A' }}</span>
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ $result->total ?? 'N/A' }}%</span>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">No published results yet.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </section>

            {{-- ATTENDANCE --}}
            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Attendance</h2>
                    <a href="{{ route('parent.children.attendance', $first) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Attendance</a>
                </div>
                <x-ui.card>
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $first->full_name }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Present: {{ $first->metrics['present'] }}
                                &middot; Total records: {{ $first->metrics['total_records'] }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Attendance Rate</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $first->metrics['attendance_rate'] }}%</p>
                        </div>
                    </div>
                </x-ui.card>
            </section>

            {{-- FEES --}}
            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Fees</h2>
                    <a href="{{ route('parent.children.fees', $first) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Fee Details</a>
                </div>
                <x-ui.card>
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $first->full_name }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                {{ $first->metrics['outstanding'] > 0 ? 'Outstanding' : 'All paid' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Outstanding</p>
                            <p class="text-2xl font-bold {{ $first->metrics['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                ₦{{ number_format($first->metrics['outstanding'], 2) }}
                            </p>
                        </div>
                    </div>
                </x-ui.card>
            </section>
        @endif

        {{-- ANNOUNCEMENTS --}}
        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h2>
                <a href="{{ route('parent.announcements') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
            </div>
            <div class="grid grid-cols-1 gap-3">
                @forelse($announcements as $announcement)
                    <x-ui.card>
                        <div class="py-1">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $announcement->title }}</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">{{ $announcement->created_at->format('F j, Y') }}</p>
                        </div>
                    </x-ui.card>
                @empty
                    <x-ui.empty-state title="No announcements" description="There are no announcements for parents at this time." />
                @endforelse
            </div>
        </section>

        {{-- RECENT REPORT CARDS --}}
        @if($children->count() > 0)
            <section>
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-3">Recent Report Cards</h2>
                <div class="grid grid-cols-1 gap-3">
                    @foreach($children as $child)
                        @php $published = $child->reportCards->where('is_published', true)->first(); @endphp
                        @if($published)
                            <x-ui.card>
                                <div class="py-1 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $published->term->name ?? 'Term' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $child->full_name }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Published</span>
                                        <a href="{{ route('parent.children.report-cards.download', [$child->id, $published->id]) }}" class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200" aria-label="Download PDF">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 19V5m0 0L7 10m5-5l5 5"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </x-ui.card>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
