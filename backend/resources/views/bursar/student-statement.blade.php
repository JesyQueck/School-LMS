<x-layouts.app title="Student Statement - {{ $student->full_name }}">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/bursar/fee-collections">Fee Collections</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Student Statement</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Student Statement</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $student->full_name }} ({{ $student->admission_no }}) - {{ $student->schoolClass->name ?? 'N/A' }}</p>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-end gap-4 mb-6">
        <form method="GET" action="{{ route('bursar.reports.student-statement', $student) }}" class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="flex-1">
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Term</label>
                <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Filter</button>
            </div>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('bursar.reports.student-statement', ['student' => $student, 'format' => 'pdf']) }}" class="bg-danger-600 hover:bg-danger-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Export PDF</a>
            <a href="{{ route('bursar.reports.student-statement', ['student' => $student, 'format' => 'csv']) }}" class="bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Export CSV</a>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Summary</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Expected</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white">₦{{ number_format($summary['total_expected'], 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Paid</p>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">₦{{ number_format($summary['total_paid'], 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Outstanding</p>
                <p class="text-2xl font-bold {{ $summary['total_outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">₦{{ number_format($summary['total_outstanding'], 2) }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Fee Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Term</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Expected</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Outstanding</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($statement as $fee)
                        @php
                            $statusConfig = match($fee['status']) {
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                'partial' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Partial'],
                                default => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Unpaid'],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">{{ $fee['fee_type'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee['term'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($fee['expected'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($fee['paid'], 2) }}</td>
                            <td class="px-4 py-3 text-sm {{ $fee['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }} text-right font-medium">₦{{ number_format($fee['outstanding'], 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No fee records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Payment History --}}
        @if($statement->flatMap(fn ($f) => $f['payments'])->count() > 0)
            <div class="px-6 py-4 border-t border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Payment History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Receipt No.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Term</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                        @foreach($statement as $fee)
                            @foreach($fee['payments'] as $payment)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment['date'] }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">{{ $payment['receipt_number'] }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee['fee_type'] }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee['term'] }}</td>
                                    <td class="px-4 py-3 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($payment['amount'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 capitalize">{{ $payment['method'] }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment['reference'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment['recorded_by'] }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>