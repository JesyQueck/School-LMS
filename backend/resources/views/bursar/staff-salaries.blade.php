<x-layouts.app title="Staff Salary Payments">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Staff Salary Payments</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Staff Salary Payments</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View eligible employees, payroll periods, prepare payroll, record payments, and view payslips.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Eligible Employees" :value="$employees->count()" icon="users" compact="true" />
        <x-ui.stat-card label="Available Periods" :value="$periods->count()" icon="calendar" compact="true" />
        <x-ui.stat-card label="Pending Approval" :value="$recentRecords->where('status', 'approved')->count()" icon="clock" compact="true" />
        <x-ui.stat-card label="Paid" :value="$recentRecords->where('status', 'paid')->count()" icon="wallet" compact="true" />
    </div>

    {{-- Quick Action Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-1">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Eligible Employees</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">View staff and salary info.</p>
                </div>
                <div class="p-5 flex-1 flex flex-col gap-3">
                    <a href="{{ route('bursar.staff-salaries.employees') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/5 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2m4 4v-2m0 0h-4m4 0H9l-5 5V5h2l2 3v-2a4 4 0 118 0 4 4 0 011 0z"/></svg></div>
                        <div class="flex-1"><p class="text-sm font-medium text-neutral-900 dark:text-white">View Employees</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Eligible staff &amp; salaries</p></div>
                    </a>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Payroll Periods</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">View available payroll periods.</p>
                </div>
                <div class="p-5 flex-1 flex flex-col gap-3">
                    <a href="{{ route('bursar.staff-salaries.periods') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/5 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                        <div class="flex-1"><p class="text-sm font-medium text-neutral-900 dark:text-white">View Periods</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Draft &amp; processed</p></div>
                    </a>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Payment History</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">View paid payroll records.</p>
                </div>
                <div class="p-5 flex-1 flex flex-col gap-3">
                    <a href="{{ route('bursar.staff-salaries.payment-history') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/5 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-success-200 dark:bg-success-900/30 text-success-700 dark:text-success-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599-1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div class="flex-1"><p class="text-sm font-medium text-neutral-900 dark:text-white">Payment History</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Paid records</p></div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Recent Records --}}
    <x-ui.card class="mb-6" elevated>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Recent Payroll Records</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Latest approved and paid records.</p>
            </div>
            <a href="{{ route('bursar.staff-salaries.payment-history') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Period</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Net Pay</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Payslip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($recentRecords as $record)
                        @php
                            $statusConfig = match($record->status) {
                                'approved' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'Approved'],
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                default => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300', 'label' => ucfirst($record->status)],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $record->employee->employee_number }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $record->period->name }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($record->net_pay, 2) }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-left">
                                <a href="{{ route('bursar.staff-salaries.payslip', $record) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Payslip</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No recent payroll records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
