<x-layouts.app title="Payroll Breakdown - Staff Salaries">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries') }}">Staff Salary Payments</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries.records', $payrollRecord->payroll_period_id) }}">Payroll Records</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Breakdown</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payroll Breakdown</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $payrollRecord->employee->employee_number }} &middot; {{ $payrollRecord->period->name }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <x-ui.card class="mb-6" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Component Breakdown</h3>
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

        <div class="lg:col-span-1">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Record Info</h3>
                </div>
                <div class="p-5 flex-1 grid grid-cols-1 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Employee</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->employee->employee_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Period</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->period->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ ucfirst($payrollRecord->status) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Pay Date</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $payrollRecord->period->pay_date->format('d M Y') }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="mt-6 flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Payment</h3>
                </div>
                <div class="p-5 flex-1 flex flex-col gap-3">
                    @if($payrollRecord->status === 'approved')
                        <form method="POST" action="{{ route('bursar.staff-salaries.payment', $payrollRecord) }}" class="w-full">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Payment Method <span class="text-danger-500">*</span></label>
                                    <select name="payment_method" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="mobile_money">Mobile Money</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Reference</label>
                                    <input name="payment_reference" type="text" placeholder="Transaction reference" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Paid At</label>
                                    <input name="paid_at" type="datetime-local" required value="{{ old('paid_at', now()->format('Y-m-d\H:i')) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <button type="submit" class="w-full bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Record Payment</button>
                            </div>
                        </form>
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
                    @else
                        <div class="w-full text-center py-4">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Payment can only be recorded once the payroll is approved by an admin.</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('bursar.staff-salaries.records', $payrollRecord->payroll_period_id) }}" class="text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">Back to Records</a>
    </div>
</x-layouts.app>
