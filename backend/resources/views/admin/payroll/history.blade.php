<x-layouts.app title="Payroll History">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Payroll History</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payroll History</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View all processed payroll records with approval and payment status.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    <x-ui.card class="mb-6" elevated>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Period</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Basic Salary</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Allowances</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Deductions</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Net Pay</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($payrolls as $payroll)
                        @php
                            $statusConfig = match($payroll->status) {
                                'calculated' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Calculated'],
                                'approved' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'Approved'],
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                'voided' => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Voided'],
                                default => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300', 'label' => ucfirst($payroll->status)],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $payroll->employee->employee_number }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $payroll->period->name }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($payroll->basic_salary_snapshot, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($payroll->total_allowances, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($payroll->total_deductions, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white text-right">₦{{ number_format($payroll->net_pay, 2) }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-left">
                                <a href="{{ route('admin.payroll.records.show', $payroll) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payroll records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-dark-border">
            {{ $payrolls->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>
