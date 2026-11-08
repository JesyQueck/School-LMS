<x-layouts.app title="Bursar Dashboard">
    @php
        $greeting = 'Good ' . (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening'));
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $greeting }}, {{ $user->name }} 👋</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Greenfield Academy - Bursar Portal</p>
        <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-1">{{ $sessionName ?? '' }} &middot; {{ $termName ?? '' }}</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    {{-- Finance Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Total Expected" :value="'₦' . number_format($finance['expected'], 2)" icon="wallet" compact="true" />
        <x-ui.stat-card label="Amount Collected" :value="'₦' . number_format($finance['collected'], 2)" icon="wallet" compact="true" :trend="['direction' => 'up', 'value' => 'Collected']" />
        <x-ui.stat-card label="Still Owed" :value="'₦' . number_format($finance['outstanding'], 2)" icon="wallet" compact="true" :trend="['direction' => 'down', 'value' => 'Outstanding']" />
        <x-ui.stat-card label="Collection Rate" :value="$finance['collection_rate'] . '%'" icon="chart" compact="true" :trend="['direction' => $finance['collection_rate'] >= 70 ? 'up' : 'neutral', 'value' => 'of fees collected']" />
    </div>

    {{-- Row 1: Fees by Class + Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-stretch">
        {{-- Fees by Class --}}
        <div class="lg:col-span-8">
            <x-ui.card class="h-full">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Fees by Class</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Breakdown of expected, collected, and outstanding amounts per class.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Students</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Expected</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Collected</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Still Owed</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @forelse($classSummary as $cs)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $cs['class'] }}</td>
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $cs['total'] }}</td>
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['expected'], 2) }}</td>
                                    <td class="px-6 py-3.5 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($cs['collected'], 2) }}</td>
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['outstanding'], 2) }}</td>
                                    <td class="px-6 py-3.5 text-right">
                                        <span class="inline-flex items-center rounded-full {{ $cs['collection_rate'] >= 70 ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : ($cs['collection_rate'] >= 40 ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300' : 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300') }} px-2 py-0.5 text-xs font-medium">
                                            {{ $cs['collection_rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No classes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        {{-- Quick Actions --}}
        <div class="lg:col-span-4">
            <x-ui.card class="h-full">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Quick Actions</h3>
                </div>
                <div class="p-6 grid grid-cols-1 gap-3 flex-1">
                    <a href="{{ route('bursar.student-fees') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Student Fees</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Record payments</p></div>
                    </a>
                    <a href="{{ route('bursar.fee-collections') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-success-200 dark:bg-success-900/30 text-success-700 dark:text-success-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Collections</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Reports & exports</p></div>
                    </a>
                    <a href="{{ route('bursar.staff-salaries') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-warning-200 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Staff Salaries</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Coming soon</p></div>
                    </a>
                    <a href="{{ route('bursar.expenses') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-danger-200 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">School Expenses</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Coming soon</p></div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Row 2: Outstanding Balances + Recent Payments --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-stretch">
        {{-- Outstanding Fees --}}
        <div class="lg:col-span-8">
            <x-ui.card class="h-full">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Outstanding Balances</h3>
                    <a href="{{ route('bursar.student-fees') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Class</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Fee Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Term</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Expected</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Paid</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Balance</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @forelse($outstandingFees as $fee)
                                @php
                                    $paid = $fee->payments->sum('amount_paid');
                                    $balance = max(0, ($fee->amount_expected ?? 0) - $paid);
                                    $statusConfig = match($fee->status ?? 'unpaid') {
                                        'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                        'partial' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Partial'],
                                        default => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Unpaid'],
                                    };
                                @endphp
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">{{ $fee->student->full_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->student->schoolClass->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->feeType->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->term->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($fee->amount_expected, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($paid, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($balance, 2) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No outstanding fees.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        {{-- Recent Payments --}}
        <div class="lg:col-span-4">
            <x-ui.card class="h-full">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Recent Payments</h3>
                    <a href="{{ route('bursar.student-fees') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                </div>
                <div class="p-6 flex-1 overflow-y-auto">
                    @forelse($recentPayments as $payment)
                        <div class="flex items-start justify-between gap-3 py-3 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $payment->studentFee->student->full_name ?? 'N/A' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                                    {{ $payment->studentFee->feeType->name }} &middot; {{ $payment->payment_date?->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-success-600 dark:text-success-400">₦{{ number_format($payment->amount_paid, 2) }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ ucfirst($payment->payment_method ?? 'Cash') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No recent payments.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>