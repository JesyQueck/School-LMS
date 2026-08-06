<x-layouts.app title="Parent Dashboard">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">Track your child's progress and activities at Greenfield Academy.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <x-ui.stat-card label="Attendance" value="98%" :trend="['direction' => 'up', 'value' => 'Perfect this month']" icon="calendar-check" />
        <x-ui.stat-card label="Average Grade" value="A-" :trend="['direction' => 'up', 'value' => 'Improved from B+']" icon="clipboard-list" />
        <x-ui.stat-card label="Fees Due" value="$250" :trend="['direction' => 'neutral', 'value' => 'Due in 5 days']" icon="wallet" />
        <x-ui.stat-card label="Announcements" value="3" :trend="['direction' => 'neutral', 'value' => '2 unread']" icon="megaphone" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Recent Results</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b-2 border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Mathematics</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Mid-term Exam</p>
                            </div>
                            <span class="text-sm font-bold text-success-600 dark:text-success-400">A</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b-2 border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Science</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Mid-term Exam</p>
                            </div>
                            <span class="text-sm font-bold text-success-600 dark:text-success-400">A-</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b-2 border-neutral-100 dark:border-neutral-800">
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">English</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Mid-term Exam</p>
                            </div>
                            <span class="text-sm font-bold text-primary-600 dark:text-primary-400">B+</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
        <div class="lg:col-span-4">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Fee Status</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Balance</span>
                        <span class="text-2xl font-bold text-neutral-900 dark:text-white">$250</span>
                    </div>
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Due Date</span>
                        <span class="text-sm font-semibold text-neutral-900 dark:text-white">Aug 15, 2026</span>
                    </div>
                    <x-ui.button variant="primary" class="w-full">Pay Now</x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
