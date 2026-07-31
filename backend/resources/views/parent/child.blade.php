<x-layouts.app title="Child Overview">
    @php
        $breadcrumbs = [
            ['label' => 'Parent', 'href' => '/parent/dashboard'],
            ['label' => $student->full_name ?? 'Child', 'active' => true],
        ];
    @endphp

    <x-slot:title>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <x-ui.breadcrumbs>
                    @foreach($breadcrumbs as $crumb)
                        <x-ui.breadcrumb-item :href="$crumb['href'] ?? null" :active="$crumb['active'] ?? false">
                            {{ $crumb['label'] }}
                        </x-ui.breadcrumb-item>
                    @endforeach
                </x-ui.breadcrumbs>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">{{ $student->full_name ?? 'Student' }}</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $student->class->name ?? 'N/A' }} &middot; {{ $student->admission_no ?? 'N/A' }}</p>
            </div>
        </div>
    </x-slot:title>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Attendance" value="98%" :trend="['direction' => 'up', 'value' => 'This term']" icon="calendar-check" />
        <x-ui.stat-card label="Average Grade" value="A-" :trend="['direction' => 'up', 'value' => 'Improved']" icon="clipboard-list" />
        <x-ui.stat-card label="Class Rank" value="#3" :trend="['direction' => 'up', 'value' => 'Up 2']" icon="users" />
        <x-ui.stat-card label="Report Cards" :value="$student->reportCards->count()" :trend="['direction' => 'neutral', 'value' => 'Published']" icon="file-text" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Published Report Cards</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Official academic performance records.</p>
                </div>
                <div class="p-6">
                    @forelse($student->reportCards as $reportCard)
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800 last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $reportCard->term->name ?? 'Term ' . $reportCard->term_id }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Position: {{ $reportCard->position_in_class ?? 'N/A' }} of {{ $reportCard->total_students_in_class ?? 'N/A' }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Published</span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">No published report cards yet.</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Report cards will appear here once published by the school.</p>
                        </div>
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
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">View Results</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Academic performance</p>
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
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Fee Status</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Payments and balances</p>
                        </div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
