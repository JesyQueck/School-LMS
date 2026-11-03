<x-layouts.app title="Outstanding Debtors Report">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/bursar/fee-collections">Fee Collections</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Outstanding Debtors</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Outstanding Debtors Report</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Students with unpaid or partially paid fees.</p>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-end gap-4 mb-6">
        <form method="GET" action="{{ route('bursar.reports.debtors') }}" class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="flex-1">
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Term</label>
                <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Class</label>
                <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Filter</button>
            </div>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('bursar.reports.debtors', ['format' => 'pdf']) }}" class="bg-danger-600 hover:bg-danger-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Export PDF</a>
            <a href="{{ route('bursar.reports.debtors', ['format' => 'csv']) }}" class="bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Export CSV</a>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Summary</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Outstanding</p>
                <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">₦{{ number_format($summary['total_outstanding'], 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Students Owing</p>
                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $summary['total_students'] }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Fee Records</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $summary['total_fee_records'] }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">By Class</h3>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Students</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee Records</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Expected</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Collected</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Outstanding</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($classSummaries as $cs)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">{{ $cs['class'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 text-right">{{ $cs['students'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 text-right">{{ $cs['fee_records'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['expected'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($cs['collected'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-danger-600 dark:text-danger-400 text-right font-medium">₦{{ number_format($cs['outstanding'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Debtor Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Admission No.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Term</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Expected</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Outstanding</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($debtors as $debtor)
                        @php
                            $statusConfig = match($debtor['status']) {
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                'partial' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Partial'],
                                default => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Unpaid'],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">{{ $debtor['student_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $debtor['admission_no'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $debtor['class'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $debtor['fee_type'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $debtor['term'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($debtor['expected'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($debtor['paid'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-danger-600 dark:text-danger-400 text-right font-medium">₦{{ number_format($debtor['outstanding'], 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No outstanding balances.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>