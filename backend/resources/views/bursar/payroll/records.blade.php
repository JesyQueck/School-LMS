<x-layouts.app title="Payroll Records - Staff Salaries">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries') }}">Staff Salary Payments</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries.periods') }}">Payroll Periods</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Payroll Records</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payroll Records</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Review calculated payroll records for {{ $period->name }}.</p>
    </div>

    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex-1 min-w-0">
            <form method="GET" action="{{ route('bursar.staff-salaries.records', $period) }}" class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm text-sm transition-colors">Filter</button>
                <a href="{{ route('bursar.staff-salaries.records', $period) }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm px-2 py-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Reset</a>
            </form>
        </div>
    </div>

    <x-ui.card class="mb-6" elevated>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Employee</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Basic</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Allowances</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Deductions</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Net Pay</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($records as $record)
                        @php
                            $statusConfig = match($record->status) {
                                'calculated' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300'],
                                'approved' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300'],
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300'],
                                'voided' => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300'],
                                default => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300'],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $record->employee->employee_number }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($record->basic_salary_snapshot, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($record->total_allowances, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($record->total_deductions, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white text-right">₦{{ number_format($record->net_pay, 2) }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ ucfirst($record->status) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('bursar.staff-salaries.breakdown', $record) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Breakdown</a>
                                @if($record->status === 'paid')
                                    <a href="{{ route('bursar.staff-salaries.payslip', $record) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline ml-2">Payslip</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payroll records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-dark-border">
            {{ $records->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>
