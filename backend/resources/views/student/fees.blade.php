<x-layouts.app title="My Fees">
    @php
        $breadcrumbs = [
            ['label' => 'Student', 'href' => '/student/dashboard'],
            ['label' => 'Fees', 'active' => true],
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
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Fees</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $student->full_name ?? 'Student' }} &middot; {{ $student->class->name ?? 'N/A' }}</p>
            </div>
        </div>
    </x-slot:title>

    <div class="grid grid-cols-1 gap-6">
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Fee Summary</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Your fee obligations and payment history.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Fee Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Term</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Expected</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Paid</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Balance</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                        @forelse($fees as $fee)
                            @php
                                $expected = $fee->amount_expected ?? 0;
                                $paid = $fee->payments->sum('amount_paid') ?? 0;
                                $balance = max(0, $expected - $paid);
                                $status = strtolower($fee->status ?? ($balance <= 0 ? 'paid' : 'unpaid'));
                                $statusConfig = match($status) {
                                    'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                    'partial' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Partial'],
                                    default => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Unpaid'],
                                };
                            @endphp
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $fee->feeType->name ?? 'Fee' }}</td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->term->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">₦{{ number_format($expected, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-success-600 dark:text-success-400">₦{{ number_format($paid, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">₦{{ number_format($balance, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No fee records found.</p>
                                        <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Fee information will appear here once assigned.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
