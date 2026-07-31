<x-layouts.app title="Teacher Dashboard">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Here's your teaching overview.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Classes Today" value="4" :trend="['direction' => 'neutral', 'value' => '2 more tomorrow']" icon="school" />
        <x-ui.stat-card label="Total Students" value="120" :trend="['direction' => 'neutral', 'value' => 'Across 4 classes']" icon="users" />
        <x-ui.stat-card label="Pending Grading" value="8" :trend="['direction' => 'down', 'value' => '3 due today']" icon="clipboard-list" />
        <x-ui.stat-card label="Attendance" value="96%" :trend="['direction' => 'up', 'value' => '1.2% from last week']" icon="calendar-check" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Today's Schedule</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Class 10-A</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Mathematics</p>
                            </div>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">8:00 - 9:00</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Class 10-B</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Mathematics</p>
                            </div>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">9:00 - 10:00</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Class 9-A</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Physics</p>
                            </div>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">10:30 - 11:30</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Pending Tasks</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-warning-500"></div>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">Grade Quiz 3 results</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-danger-500"></div>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">Submit term report</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-primary-500"></div>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">Review assignment submissions</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
