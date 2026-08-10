<x-layouts.app title="Child Overview">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/parent/dashboard">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>{{ $student->full_name ?? 'Child' }}</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">{{ $student->full_name ?? 'Student' }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $student->class->name ?? 'N/A' }} &middot; {{ $student->admission_no ?? 'N/A' }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Attendance" :value="$metrics['attendance_rate'] . '%'" icon="calendar-check" />
        <x-ui.stat-card label="Average" :value="$metrics['average_score'] . '%'" icon="clipboard-list" />
        <x-ui.stat-card label="Present / Total" :value="$metrics['present'] . ' / ' . $metrics['total_records']" icon="users" />
        <x-ui.stat-card label="Outstanding" :value="'₦' . number_format($metrics['outstanding'], 2)" icon="wallet" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
            {{-- Profile --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Profile</h3>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Date of Birth</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->date_of_birth ? $student->date_of_birth->format('j F Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Gender</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($student->gender ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Class</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->class->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Admission No</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->admission_no ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Status</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($student->status ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">State of Origin</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->state_of_origin ?? 'N/A' }}</p>
                    </div>
                </div>
            </x-ui.card>

            {{-- Report Cards by term --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Report Cards</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Published report cards are shown here. Unpublished terms are not available.</p>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($terms as $term)
                        @php $rc = $term->reportCards->first(); @endphp
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800 last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $term->name ?? 'Term' }}</p>
                                @if($rc && $rc->is_published)
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Position: {{ $rc->position_in_class ?? 'N/A' }} of {{ $rc->total_students_in_class ?? 'N/A' }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                @if($rc && $rc->is_published)
                                    <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Published</span>
                                    <a href="{{ route('parent.children.report-cards.download', [$student->id, $rc->id]) }}" class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200" aria-label="Download PDF">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 19V5m0 0L7 10m5-5l5 5"/></svg>
                                    </a>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-2.5 py-0.5 text-xs font-medium text-neutral-500 dark:text-neutral-400">Not available yet</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No terms configured.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Quick Links</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('parent.children.results', $student) }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Academic</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">View results</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.children.attendance', $student) }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Attendance</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Daily records</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.children.fees', $student) }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Fees</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Payments and balances</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.children.report-cards', $student) }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Report Cards</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Academic records</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.timetable') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Timetable</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Weekly schedule</p>
                        </div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
