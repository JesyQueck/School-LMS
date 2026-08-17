<x-layouts.app title="Admin Dashboard">
    @php
        $user = auth()->user();
        $greeting = 'Good ' . (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening'));
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $greeting }}, {{ $user->name }} 👋</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Greenfield Academy</p>
        <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-1">{{ $session }} &middot; {{ $termName }}</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    {{-- School Statistics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Students" :value="number_format($totalStudents)" icon="user" />
        <x-ui.stat-card label="Teachers" :value="number_format($totalTeachers)" icon="graduation-cap" />
        <x-ui.stat-card label="Classes" :value="number_format($totalClasses)" icon="school" />
        <x-ui.stat-card label="Parents" :value="number_format($totalParents)" icon="users" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        {{-- Academic Overview --}}
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Academic Overview</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Current academic setup and teacher workload.</p>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Active Classes</p>
                        <p class="mt-1 text-2xl font-bold text-neutral-900 dark:text-white">{{ $activeClasses }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Subjects</p>
                        <p class="mt-1 text-2xl font-bold text-neutral-900 dark:text-white">{{ $subjects }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Teachers Assigned</p>
                        <p class="mt-1 text-2xl font-bold text-neutral-900 dark:text-white">{{ $teachersAssigned }}/{{ $totalTeachersForAssignment }}</p>
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-3">Results</p>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-700 dark:text-neutral-300">Submitted</span>
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ $resultsSubmitted }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                                <div class="h-full rounded-full bg-primary-500" style="width: {{ $resultsSubmitted > 0 ? 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-700 dark:text-neutral-300">Locked</span>
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ $resultsLocked }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                                <div class="h-full rounded-full bg-success-500" style="width: {{ $resultsSubmitted > 0 ? round(($resultsLocked / $resultsSubmitted) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-700 dark:text-neutral-300">Pending</span>
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ $resultsPending }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                                <div class="h-full rounded-full bg-warning-500" style="width: {{ $resultsSubmitted > 0 ? round(($resultsPending / $resultsSubmitted) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Finance Overview --}}
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Finance Overview</h3>
                </div>
                <div class="p-6 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Expected Fees</span>
                        <span class="font-medium text-neutral-900 dark:text-white">₦{{ number_format($finance['expected'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Collected</span>
                        <span class="font-medium text-success-600 dark:text-success-400">₦{{ number_format($finance['collected'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Outstanding</span>
                        <span class="font-medium {{ $finance['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">₦{{ number_format($finance['outstanding'], 2) }}</span>
                    </div>
                    <div class="pt-3 border-t border-neutral-100 dark:border-dark-border">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-neutral-500 dark:text-neutral-400">Collection Rate</span>
                            <span class="font-semibold text-neutral-900 dark:text-white">{{ $finance['collection_rate'] }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                            <div class="h-full rounded-full bg-primary-500" style="width: {{ $finance['collection_rate'] }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-6 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-lg bg-success-50 dark:bg-success-900/20 p-2">
                        <p class="text-lg font-bold text-success-700 dark:text-success-300">{{ $finance['paid'] }}</p>
                        <p class="text-[10px] text-neutral-500 dark:text-neutral-400 uppercase">Paid</p>
                    </div>
                    <div class="rounded-lg bg-warning-50 dark:bg-warning-900/20 p-2">
                        <p class="text-lg font-bold text-warning-700 dark:text-warning-300">{{ $finance['partial'] }}</p>
                        <p class="text-[10px] text-neutral-500 dark:text-neutral-400 uppercase">Partial</p>
                    </div>
                    <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 p-2">
                        <p class="text-lg font-bold text-danger-700 dark:text-danger-300">{{ $finance['unpaid'] }}</p>
                        <p class="text-[10px] text-neutral-500 dark:text-neutral-400 uppercase">Unpaid</p>
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <a href="{{ route('admin.finance') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Manage Fees</a>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Recent Activity</h3>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Audit Logs</a>
                </div>
                <div class="p-6">
                    @forelse($recentActivity as $activity)
                        <div class="flex items-start gap-3 py-3 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
                            <div class="h-2 w-2 mt-2 rounded-full bg-primary-500 shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $activity->action }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                                    {{ $activity->user->name ?? 'System' }} &middot; {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No recent activity.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- Quick Links --}}
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Quick Actions</h3>
                </div>
                <div class="p-6 grid grid-cols-1 gap-3">
                    <a href="{{ route('admin.students') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Students</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Manage records</p></div>
                    </a>
                    <a href="{{ route('admin.teachers') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Teachers</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Assign classes</p></div>
                    </a>
                    <a href="{{ route('admin.finance') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Finance</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Fees & payments</p></div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
