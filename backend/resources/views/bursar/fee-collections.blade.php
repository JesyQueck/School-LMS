<x-layouts.app title="Fee Collections & Reports">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Fee Collections & Reports</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Fee Collections & Reports</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Daily collections, payment methods, and outstanding debtors.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Today's Collections" :value="'₦' . number_format($dailyCollections[0]['amount'] ?? 0, 2)" icon="wallet" compact="true" />
        <x-ui.stat-card label="Today's Transactions" :value="$dailyCollections[0]['count'] ?? 0" icon="receipt" compact="true" />
        <x-ui.stat-card label="Total Outstanding" :value="'₦' . number_format($outstandingDebtors->sum('outstanding'), 2)" icon="alert-triangle" compact="true" :trend="['direction' => 'down', 'value' => 'Outstanding']" />
        <x-ui.stat-card label="Debtors" :value="$outstandingDebtors->unique('admission_no')->count()" icon="users" compact="true" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-stretch">
        <div class="lg:col-span-7">
            {{-- Daily Collections --}}
            <x-ui.card class="h-full flex flex-col">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Daily Collections</h3>
                    @if($currentTerm)
    <a href="{{ route('bursar.reports.daily', ['term_id' => $currentTerm->id]) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Report</a>
@endif
                </div>
                <div class="p-6 flex-1 overflow-y-auto">
                    @if(!empty($dailyCollections))
                        <div class="space-y-3">
                            @foreach(array_slice($dailyCollections, 0, 10) as $day)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 dark:border-dark-border">
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $day['count'] }} transactions</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-success-600 dark:text-success-400">₦{{ number_format($day['amount'], 2) }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            @foreach($day['by_method'] as $method => $data)
                                                {{ ucfirst($method) }}: ₦{{ number_format($data['amount'], 2) }}{{ $loop->last ? '' : ' | ' }}
                                            @endforeach
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-8">No collections recorded yet.</p>
                    @endif
                </div>
            </x-ui.card>

            {{-- Outstanding Debtors --}}
            <x-ui.card class="h-full flex flex-col mt-6">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Outstanding Debtors (Top 50)</h3>
                    <a href="{{ route('bursar.reports.debtors') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Report</a>
                </div>
                <div class="p-6 flex-1 overflow-y-auto">
                    @if($outstandingDebtors->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                                <thead class="bg-neutral-50 dark:bg-dark-surface">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Expected</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Paid</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Balance</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                    @foreach($outstandingDebtors as $debtor)
                                        @php
                                            $statusConfig = match($debtor['status']) {
                                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                                'partial' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Partial'],
                                                default => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Unpaid'],
                                            };
                                        @endphp
                                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                            <td class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $debtor['student_name'] }}</td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $debtor['class'] }}</td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $debtor['fee_type'] }}</td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($debtor['expected'], 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($debtor['paid'], 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-danger-600 dark:text-danger-400 text-right font-medium">₦{{ number_format($debtor['outstanding'], 2) }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-8">No outstanding balances.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-5">
            {{-- Payment Method Summary --}}
            <x-ui.card class="mb-6">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Payment Method Summary</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Breakdown by payment method for {{ $currentTerm->name ?? 'current term' }}.</p>
                </div>
                <div class="p-6">
                    @if(!empty($paymentMethodSummary))
                        <div class="space-y-3">
                            @foreach($paymentMethodSummary as $method => $data)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 dark:border-dark-border">
                                    <div class="flex items-center gap-3">
@php
    $methodClass = match($method) {
        'cash' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
        'bank_transfer' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
        'card' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
        'cheque' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
        default => 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300',
    };
@endphp
{{ $methodClass }} flex items-center justify-center">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($method === 'cash')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @elseif($method === 'bank_transfer')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                @elseif($method === 'card')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                @endif
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-neutral-900 dark:text-white capitalize">{{ $method }}</p>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $data['count'] }} transactions</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-success-600 dark:text-success-400">₦{{ number_format($data['amount'], 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No payment data available.</p>
                    @endif
                </div>
            </x-ui.card>

            {{-- Class Collection Summary --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Class Collection Summary</h3>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Expected</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Collected</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Outstanding</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @forelse($classCollectionSummary as $cs)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $cs['class'] }}</td>
                                    <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['expected'], 2) }}</td>
                                    <td class="px-3 py-2 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($cs['collected'], 2) }}</td>
                                    <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['outstanding'], 2) }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <span class="inline-flex items-center rounded-full {{ $cs['collection_rate'] >= 70 ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : ($cs['collection_rate'] >= 40 ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300' : 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300') }} px-2 py-0.5 text-xs font-medium">
                                            {{ $cs['collection_rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No class data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Quick Report Actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @if($currentTerm)
    <a href="{{ route('bursar.reports.daily', ['term_id' => $currentTerm->id]) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Report</a>
@endif
            <div class="h-10 w-10 rounded-lg bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
            <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Daily Collection Report</p><p class="text-xs text-neutral-500 dark:text-neutral-400">PDF / CSV Export</p></div>
        </a>
        <a href="{{ route('bursar.reports.debtors') }}" class="flex items-center gap-3 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
            <div class="h-10 w-10 rounded-lg bg-danger-200 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg></div>
            <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Outstanding Debtors</p><p class="text-xs text-neutral-500 dark:text-neutral-400">PDF / CSV Export</p></div>
        </a>
        <a href="{{ route('bursar.student-fees') }}" class="flex items-center gap-3 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
            <div class="h-10 w-10 rounded-lg bg-success-200 dark:bg-success-900/30 text-success-700 dark:text-success-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
            <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Student Fee Payments</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Record new payments</p></div>
        </a>
    </div>
</x-layouts.app>