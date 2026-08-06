<x-layouts.app title="Admin Dashboard">
    @php
        $stats = [
            ['label' => 'Total Students', 'value' => number_format($students), 'trend' => ['direction' => 'up', 'value' => 'Active enrollments'], 'icon' => 'users'],
            ['label' => 'Total Teachers', 'value' => number_format($teachers), 'trend' => ['direction' => 'neutral', 'value' => 'Teaching staff'], 'icon' => 'graduation-cap'],
            ['label' => 'Total Classes', 'value' => number_format($classes), 'trend' => ['direction' => 'neutral', 'value' => 'Across all grades'], 'icon' => 'school'],
        ];
    @endphp

    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Dashboard Overview</h2>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Welcome back, {{ Auth::user()->name }}. Here's what's happening at your school today.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-1 text-xs font-medium text-success-700 dark:text-success-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                    System Operational
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @foreach($stats as $stat)
                <x-ui.stat-card :label="$stat['label']" :value="$stat['value']" :trend="$stat['trend']" :icon="$stat['icon']" />
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Quick Actions</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Common administrative tasks at your fingertips.</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="{{ route('admin.accounts') }}" class="flex items-center gap-4 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors group">
                            <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Manage Accounts</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Create teacher, student, and parent accounts</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.students') }}" class="flex items-center gap-4 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors group">
                            <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Manage Students</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Add, edit, or view student records</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.teachers') }}" class="flex items-center gap-4 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors group">
                            <div class="h-10 w-10 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Manage Teachers</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Assign classes and subjects</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.finance') }}" class="flex items-center gap-4 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors group">
                            <div class="h-10 w-10 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Finance</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Manage fees and payments</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.results') }}" class="flex items-center gap-4 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors group">
                            <div class="h-10 w-10 rounded-lg bg-warning-100 dark:bg-warning-900/30 text-warning-600 dark:text-warning-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Results</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Enter and publish results</p>
                            </div>
                        </a>
                    </div>
                </div>
            </x-ui.card>
        </div>
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Latest school-wide updates.</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="h-2 w-2 mt-2 rounded-full bg-primary-500 shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Welcome Back!</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">School resumes on September 1st, 2026.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="h-2 w-2 mt-2 rounded-full bg-accent-500 shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">PTA Meeting</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">All parents are invited to the PTA meeting.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <a href="{{ route('public.announcements') }}" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">View all announcements &rarr;</a>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
