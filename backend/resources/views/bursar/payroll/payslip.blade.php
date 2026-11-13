<x-layouts.app title="Payslip - Staff Salaries">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries') }}">Staff Salary Payments</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Payslip</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payslip</h1>
    </div>

    @php
        $schoolName = config('school.name', config('app.name', 'Greenfield Academy'));
        $record = $payrollRecord;
        $employee = $record->employee;
        $period = $record->period;
        $allowances = $record->components->where('component_type', 'allowance');
        $deductions = $record->components->where('component_type', 'deduction');
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('bursar.staff-salaries.payment-history') }}" class="text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">Back to Payment History</a>
        </div>
    </div>

    {{-- Printable Payslip --}}
    <x-ui.card class="mb-6" elevated>
        <div class="px-6 py-6">
            <!-- School Header -->
            <div class="mb-8 border-b border-neutral-200 dark:border-dark-border pb-4">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-lg bg-primary-800 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(collect(explode(' ', $schoolName))->first()[0] ?? 'S') }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ $schoolName }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Payslip</p>
                    </div>
                </div>
            </div>

            <!-- Employee Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="p-4 bg-neutral-50 dark:bg-dark-surface rounded-xl">
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1">Employee Name</p>
                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $employee->user?->name ?? $employee->employee_number }}</p>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1 mt-2">Employee Number</p>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $employee->employee_number }}</p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-dark-surface rounded-xl">
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1">Payroll Period</p>
                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $period->name }}</p>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1 mt-2">Pay Date</p>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $period->pay_date->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Earnings & Deductions -->
            <div class="overflow-x-auto mb-8">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Earnings</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Deductions</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                        <tr>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">Basic Salary</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($record->basic_salary_snapshot, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">—</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">—</td>
                        </tr>
                        @foreach($allowances as $allowance)
                            <tr>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $allowance->component_name }}</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($allowance->amount, 2) }}</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">—</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">—</td>
                            </tr>
                        @endforeach
                        @foreach($deductions as $deduction)
                            <tr>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $deduction->component_name }}</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">—</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $deduction->component_name }}</td>
                                <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">-₦{{ number_format($deduction->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-neutral-50 dark:bg-dark-surface font-semibold">
                            <td class="px-6 py-3.5 text-sm text-neutral-900 dark:text-white text-right">Gross Pay:</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-900 dark:text-white text-right">₦{{ number_format($record->gross_pay, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">Total Deductions:</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($record->total_deductions, 2) }}</td>
                        </tr>
                        <tr class="bg-success-50 dark:bg-success-950/20 font-bold border border-success-200 dark:border-success-800">
                            <td class="px-6 py-3.5 text-sm text-success-800 dark:text-success-300 text-right">Net Pay:</td>
                            <td class="px-6 py-3.5 text-sm text-success-800 dark:text-success-300 text-right">₦{{ number_format($record->net_pay, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">Status:</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">{{ ucfirst($record->status) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-neutral-200 dark:border-dark-border">
                <p class="text-xs text-neutral-500 dark:text-neutral-400">This is a computer-generated payslip and does not require a signature.</p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Generated from saved payroll snapshot.</p>
            </div>
        </div>
    </x-ui.card>

    {{-- Download PDF --}}
    <div class="flex justify-end">
        <a href="{{ route('bursar.staff-salaries.payslip', $record) . '?download=1' }}" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2.5 rounded-lg shadow-sm text-sm transition-colors flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download PDF
        </a>
    </div>
</x-layouts.app>
