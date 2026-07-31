<x-layouts.app title="Student Dashboard">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/student/dashboard" active>Student</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Student Dashboard</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Welcome, {{ $student->full_name ?? 'Student' }}. Here's your academic overview.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Attendance" value="98%" :trend="['direction' => 'up', 'value' => 'This term']" icon="calendar-check" />
        <x-ui.stat-card label="Average Grade" value="A-" :trend="['direction' => 'up', 'value' => 'Improved from B+']" icon="clipboard-list" />
        <x-ui.stat-card label="Class Rank" value="#3" :trend="['direction' => 'up', 'value' => 'Up 2 positions']" icon="users" />
        <x-ui.stat-card label="Assignments" value="2" :trend="['direction' => 'down', 'value' => 'Due this week']" icon="book-open" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Upcoming Assignments</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Tasks and assignments due soon.</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Mathematics Essay</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Mr. Johnson</p>
                            </div>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300">Due in 2 days</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Science Project</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Ms. Williams</p>
                            </div>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300">Due tomorrow</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">English Comprehension</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Mr. Brown</p>
                            </div>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">Due in 5 days</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Today's Classes</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Your schedule for today.</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-primary-500 flex-shrink-0"></div>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">Mathematics — 8:00 AM</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-accent-500 flex-shrink-0"></div>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">Physics — 10:30 AM</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-success-500 flex-shrink-0"></div>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">English — 1:00 PM</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
            <x-ui.card class="mt-6">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Quick Links</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('student.results') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">My Results</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">View grades</p>
                        </div>
                    </a>
                    <a href="{{ route('student.attendance') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Attendance</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Daily records</p>
                        </div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
