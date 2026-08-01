<x-layouts.app title="Admin Dashboard">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Here's what's happening at your school today.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Total Students" value="1,234" :trend="['direction' => 'up', 'value' => '12% from last month']" icon="users" />
        <x-ui.stat-card label="Total Teachers" value="48" :trend="['direction' => 'up', 'value' => '3 new this term']" icon="graduation-cap" />
        <x-ui.stat-card label="Total Classes" value="36" :trend="['direction' => 'neutral', 'value' => 'No change']" icon="school" />
        <x-ui.stat-card label="Attendance Rate" value="94.5%" :trend="['direction' => 'up', 'value' => '2.1% from last week']" icon="calendar-check" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Attendance Overview</h3>
                </div>
                <div class="p-6">
                    <div class="h-75 flex items-center justify-center text-neutral-500 dark:text-neutral-400">
                        Chart placeholder — integrate Chart.js or ApexCharts
                    </div>
                </div>
            </x-ui.card>
        </div>
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Upcoming Events</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="h-2 w-2 mt-2 rounded-full bg-primary-500"></div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Parent-Teacher Meeting</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Tomorrow, 2:00 PM</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="h-2 w-2 mt-2 rounded-full bg-accent-500"></div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Science Fair</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Friday, 10:00 AM</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="h-2 w-2 mt-2 rounded-full bg-success-500"></div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Term Ends</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Next Friday</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
