<x-layouts.app title="Payroll Record Details">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.history') }}">Payroll History</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Payroll Record</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payroll Record Details</h1>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <div class="flex flex-col gap-2">
                <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">{{ $errors->first() }}</h4>
            </div>
        </x-ui.alert>
    @endif

    @php
        $statusConfig = match($payrollRecord->status) {
            'calculated' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Calculated'],
            'approved' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'Approved'],
            'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
            'voided' => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Voided'],
            default => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300', 'label' => ucfirst($payrollRecord->status)],
        };
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <div class="lg:col-span-8">
            <x-ui.card class="h-full" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Payroll Summary</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $payrollRecord->employee->employee_number }} &middot; {{ $payrollRecord->period->name }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-3 py-1 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Component</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Basis</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Value</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            <tr>
                                <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">Basic Salary</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">Base</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">Fixed</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">—</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($payrollRecord->basic_salary_snapshot, 2) }}</td>
                            </tr>
                            @foreach($payrollRecord->components as $component)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $component->component_name }}</td>
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($component->component_type) }}</td>
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($component->calculation_basis) }}</td>
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">{{ $component->calculation_basis === 'percentage' ? $component->calculation_value.'%' : '₦'.number_format($component->calculation_value, 2) }}</td>
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">{{ $component->component_type === 'allowance' ? '+' : '-' }}₦{{ number_format($component->amount, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-neutral-50 dark:bg-dark-surface font-semibold">
                                <td class="px-6 py-3.5 text-sm text-neutral-900 dark:text-white text-right">Total Allowances:</td>
                                <td class="px-6 py-3.5 text-right" colspan="4">₦{{ number_format($payrollRecord->total_allowances, 2) }}</td>
                            </tr>
                            <tr class="bg-neutral-50 dark:bg-dark-surface font-semibold">
                                <td class="px-6 py-3.5 text-sm text-neutral-900 dark:text-white text-right">Total Deductions:</td>
                                <td class="px-6 py-3.5 text-right" colspan="4">₦{{ number_format($payrollRecord->total_deductions, 2) }}</td>
                            </tr>
                            <tr class="bg-neutral-100 dark:bg-dark-border font-bold">
                                <td class="px-6 py-3.5 text-sm text-neutral-900 dark:text-white text-right">Gross Pay:</td>
                                <td class="px-6 py-3.5 text-right" colspan="4">₦{{ number_format($payrollRecord->gross_pay, 2) }}</td>
                            </tr>
                            <tr class="bg-success-50 dark:bg-success-950/20 font-bold border border-success-200 dark:border-success-800">
                                <td class="px-6 py-3.5 text-sm text-success-800 dark:text-success-300 text-right">Net Pay:</td>
                                <td class="px-6 py-3.5 text-right text-success-800 dark:text-success-300" colspan="4">₦{{ number_format($payrollRecord->net_pay, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-4">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Actions</h3>
                </div>
                <div class="p-5 flex-1 flex flex-col gap-3">
                    @if($payrollRecord->status === 'calculated')
                        <form method="POST" action="{{ route('admin.payroll.records.approve', $payrollRecord) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Approve Payroll</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payroll.records.reject', $payrollRecord) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-danger-600 hover:bg-danger-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Reject / Void</button>
                        </form>
                    @elseif($payrollRecord->status === 'approved')
                        <form method="POST" action="{{ route('admin.payroll.records.reject', $payrollRecord) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-danger-600 hover:bg-danger-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Reject / Void</button>
                        </form>
                        <a href="{{ route('bursar.staff-salaries.payment', $payrollRecord) }}" class="w-full bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors text-center">Record Payment (Bursar)</a>
                    @elseif($payrollRecord->status === 'paid')
                        <div class="w-full bg-success-50 dark:bg-success-950/10 border border-success-200 dark:border-success-800 rounded-lg p-4">
                            <p class="text-sm font-medium text-success-800 dark:text-success-300">Payment Recorded</p>
                            <p class="text-xs text-success-600 dark:text-success-400 mt-1">Method: {{ ucfirst($payrollRecord->payment_method ?? '—') }}</p>
                            @if($payrollRecord->payment_reference)
                                <p class="text-xs text-success-600 dark:text-success-400">Reference: {{ $payrollRecord->payment_reference }}</p>
                            @endif
                            <p class="text-xs text-success-600 dark:text-success-400">Paid by: {{ $payrollRecord->payer?->name ?? '—' }}</p>
                            <p class="text-xs text-success-600 dark:text-success-400">Paid at: {{ $payrollRecord->paid_at?->format('d M Y H:i') ?? '—' }}</p>
                        </div>
                    @endif

                    @if($payrollRecord->status === 'approved' || $payrollRecord->status === 'paid')
                        <div class="mt-auto pt-4 border-t border-neutral-200 dark:border-dark-border">
                            <a href="{{ route('bursar.staff-salaries.payslip', $payrollRecord) }}" class="w-full flex items-center justify-center gap-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                View Payslip
                            </a>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Meta info --}}
    <x-ui.card class="mb-6" elevated>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Record Information</h3>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Employee</p>
                <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->employee->employee_number }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Period</p>
                <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->period->name }} ({{ $payrollRecord->period->start_date->format('d M Y') }} - {{ $payrollRecord->period->end_date->format('d M Y') }})</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Pay Date</p>
                <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->period->pay_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</p>
                <p class="text-sm text-neutral-900 dark:text-white">{{ ucfirst($payrollRecord->status) }}</p>
            </div>
            @if($payrollRecord->approved_by)
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Approved By</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->approver->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Approved At</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->approved_at->format('d M Y H:i') }}</p>
                </div>
            @endif
            @if($payrollRecord->paid_by)
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Paid By</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->payer->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Paid At</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->paid_at->format('d M Y H:i') }}</p>
                </div>
            @endif
        </div>
    </x-ui.card>
</x-layouts.app>
