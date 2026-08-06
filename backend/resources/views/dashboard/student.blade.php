<x-layouts.app title="Student Dashboard">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">Here's your academic overview at Greenfield Academy.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <x-ui.stat-card label="Attendance" value="98%" :trend="['direction' => 'up', 'value' => 'Perfect this month']" icon="calendar-check" />
        <x-ui.stat-card label="Average Grade" value="A-" :trend="['direction' => 'up', 'value' => 'Improved from B+']" icon="clipboard-list" />
        <x-ui.stat-card label="Class Rank" value="#3" :trend="['direction' => 'up', 'value' => 'Up 2 positions']" icon="users" />
        <x-ui.stat-card label="Assignments" value="2" :trend="['direction' => 'down', 'value' => 'Due this week']" icon="book-open" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Upcoming Assignments</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b-2 border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Mathematics Essay</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Mr. Johnson</p>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300">Due in 2 days</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b-2 border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Science Project</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Ms. Williams</p>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300">Due tomorrow</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
        <div class="lg:col-span-4">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Today's Classes</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-full bg-primary-500"></div>
                            <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Mathematics — 8:00</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-full bg-accent-500"></div>
                            <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Physics — 10:30</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-full bg-success-500"></div>
                            <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">English — 1:00</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
