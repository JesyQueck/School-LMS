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
        <a href="{{ route('parent.children.index') }}" class="block">
            <x-ui.stat-card label="Children" :value="min($stats['children'], 9)" icon="school" compact valueSize="text-xl" />
        </a>
        <x-ui.stat-card label="Attendance" :value="min($stats['attendance'], 100) . '%'" icon="calendar-check" compact valueSize="text-xl" />
        <x-ui.stat-card label="Avg. Result" :value="min($stats['avg_result'], 100) . '%'" icon="clipboard-list" compact valueSize="text-xl" />
        <x-ui.stat-card label="Fees Due" :value="'₦' . number_format(min($stats['fees_due'], 999999), 0)" icon="book-open" compact valueSize="text-xl" />
    </div>

    <div class="space-y-8">
        {{-- MY CHILDREN --}}
        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 id="my-children" class="text-lg font-semibold text-neutral-900 dark:text-white">My Children</h2>
                <a href="{{ route('parent.children.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View all</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($children as $child)
                    <x-ui.card hoverable class="p-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                @if($child->user && $child->user->profile_photo)
                                    <img src="{{ asset('storage/' . $child->user->profile_photo) }}" alt="{{ $child->full_name }}" class="h-16 w-16 rounded-full object-cover">
                                @else
                                    <div class="h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-2xl flex-shrink-0">
                                        {{ substr($child->full_name ?? $child->admission_no, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate">{{ $child->full_name ?? 'Unknown' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ $child->schoolClass->name ?? 'Unassigned' }}</p>
                                <p class="text-xs text-neutral-400 dark:text-neutral-500">Adm: {{ $child->admission_no ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-1.5 text-center">
                            <div class="p-2 rounded bg-neutral-50 dark:bg-neutral-800/50">
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400">Avg</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $child->metrics['average_score'] }}%</p>
                            </div>
                            <div class="p-2 rounded bg-neutral-50 dark:bg-neutral-800/50">
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400">Att</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $child->metrics['attendance_rate'] }}%</p>
                            </div>
                            <div class="p-2 rounded bg-neutral-50 dark:bg-neutral-800/50">
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400">Fees</p>
                                <p class="text-xs font-semibold {{ $child->metrics['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                    {{ $child->metrics['outstanding'] > 0 ? '₦' . number_format($child->metrics['outstanding'], 0) : 'Paid' }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
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

        @if($selectedChild)
            <section aria-labelledby="child-summary-heading">
                <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-400">
                    Showing summaries for <span class="font-semibold text-neutral-900 dark:text-white">{{ $selectedChild->full_name }}</span>
                    <span aria-hidden="true">&middot;</span> {{ $selectedChild->schoolClass->name ?? 'Unassigned' }}
                </p>
            </section>

            {{-- SUMMARY GRID --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                {{-- RECENT RESULTS --}}
                <x-ui.card class="h-full flex flex-col p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent Results</h3>
                        <a href="{{ route('parent.children.results', $selectedChild) }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-1.5">
                        @forelse($recentResults->take(3) as $result)
                            <div class="flex items-center justify-between text-xs px-1">
                                <div>
                                    <p class="text-neutral-700 dark:text-neutral-300 truncate max-w-[180px]">{{ $result->classSubject->subject->name ?? 'N/A' }}</p>
                                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400">{{ $result->term->name ?? 'Term' }}</p>
                                </div>
                                <span class="font-medium text-neutral-900 dark:text-white whitespace-nowrap">{{ $result->total ?? 'N/A' }}%</span>
                            </div>
                        @empty
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 text-center py-4">No published results yet.</p>
                        @endforelse
                    </div>
                </x-ui.card>

                {{-- ATTENDANCE --}}
                <x-ui.card class="h-full flex flex-col p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Attendance</h3>
                        <a href="{{ route('parent.children.attendance', $selectedChild) }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-neutral-500 dark:text-neutral-400">Rate</span>
                            <span class="font-bold text-neutral-900 dark:text-white">{{ $selectedChild->metrics['attendance_rate'] }}%</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-neutral-500 dark:text-neutral-400">Present</span>
                            <span class="text-neutral-700 dark:text-neutral-300">{{ $selectedChild->metrics['present'] }} / {{ $selectedChild->metrics['total_records'] }}</span>
                        </div>
                    </div>
                </x-ui.card>

                {{-- FEES --}}
                <x-ui.card class="h-full flex flex-col p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Fees</h3>
                        <a href="{{ route('parent.children.fees', $selectedChild) }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View Details</a>
                    </div>
                    <div class="flex-1 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-neutral-500 dark:text-neutral-400">Total</span>
                            <span class="font-medium text-neutral-900 dark:text-white">₦{{ number_format($selectedChild->metrics['total_expected'], 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-500 dark:text-neutral-400">Paid</span>
                            <span class="font-medium text-success-600 dark:text-success-400">₦{{ number_format($selectedChild->metrics['total_paid'], 0) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-neutral-100 dark:border-dark-border pt-1.5">
                            <span class="text-neutral-500 dark:text-neutral-400">Outstanding</span>
                            <span class="font-bold {{ $selectedChild->metrics['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                ₦{{ number_format($selectedChild->metrics['outstanding'], 0) }}
                            </span>
                        </div>
                    </div>
                </x-ui.card>

                {{-- ANNOUNCEMENTS --}}
                <x-ui.card class="h-full flex flex-col p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Announcements</h3>
                        <a href="{{ route('parent.announcements') }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-1.5">
                        @forelse($announcements->take(2) as $announcement)
                            <div class="px-1 py-1.5 rounded border border-neutral-100 dark:border-dark-border">
                                <p class="text-xs font-medium text-neutral-900 dark:text-white line-clamp-1">{{ $announcement->title }}</p>
                                <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-0.5">{{ $announcement->created_at->format('F j, Y') }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 text-center py-4">No announcements.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        @else
            <x-ui.empty-state title="No child summaries available" description="Once the school links a child to your account, their academic progress, attendance, and fee summaries will appear here." />
        @endif

        </div>
</x-layouts.app>
