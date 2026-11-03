<x-layouts.app title="Daily Collection Report">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/bursar/fee-collections">Fee Collections</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Daily Collection Report</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Daily Collection Report</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $term->name }} - {{ $term->academicSession->name ?? 'N/A' }}</p>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-end gap-4 mb-6">
        <form method="GET" action="{{ route('bursar.reports.daily') }}" class="flex flex-col sm:flex-row gap-3 flex-1">
            <input type="hidden" name="term_id" value="{{ $term->id }}">
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
            <a href="{{ route('bursar.reports.daily', ['term_id' => $term->id, 'format' => 'pdf']) }}" class="bg-danger-600 hover:bg-danger-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Export PDF</a>
            <a href="{{ route('bursar.reports.daily', ['term_id' => $term->id, 'format' => 'csv']) }}" class="bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Export CSV</a>
        </div>
    </div>

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Summary</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Collected</p>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">₦{{ number_format($summary['total_collected'], 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Transactions</p>
                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $summary['total_transactions'] }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Payment Methods</p>
                <p class="text-sm font-medium">
                    @foreach($summary['by_method'] as $method => $data)
                        {{ ucfirst($method) }}: {{ $data['count'] }} (₦{{ number_format($data['amount'], 2) }}){{ $loop->last ? '' : ' | ' }}
                    @endforeach
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Receipt No.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Admission No.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee Type</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($dailyData as $row)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">{{ $row['receipt_number'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $row['student_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $row['admission_no'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $row['class'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $row['fee_type'] }}</td>
                            <td class="px-4 py-3 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($row['amount_paid'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 capitalize">{{ $row['payment_method'] }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $row['reference'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ $row['recorded_by'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No collection records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>