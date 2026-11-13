<x-layouts.app title="Payroll Period Details - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.periods.index') }}">Payroll Periods</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Period Details</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payroll Period</h1>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ $period->name }}</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $period->start_date->format('d M Y') }} &mdash; {{ $period->end_date->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payroll.periods.edit', $period) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Edit</a>
            <a href="{{ route('admin.payroll.periods.index') }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm font-medium px-4 py-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <div class="lg:col-span-3">
            <x-ui.card class="mb-6" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Payroll Records</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Employee</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Net Pay</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @php
                                $allRecords = $period->payrollRecords->sortBy('employee.employee_number');
                                $calculated = $allRecords->where('status', 'calculated');
                                $approved = $allRecords->where('status', 'approved');
                                $paid = $allRecords->where('status', 'paid');
                                $voided = $allRecords->where('status', 'voided');
                            @endphp
                            @forelse($allRecords as $record)
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
                                    <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($record->net_pay, 2) }}</td>
                                    <td class="px-6 py-3.5 text-center">
                                        <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ ucfirst($record->status) }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('admin.payroll.records.show', $record) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payroll records for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Period Summary</h3>
                </div>
                <div class="p-5 flex-1 grid grid-cols-1 gap-4">
                    <div class="p-4 border border-neutral-200 dark:border-dark-border rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Total Records</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $allRecords->count() }}</p>
                    </div>
                    <div class="p-4 border border-neutral-200 dark:border-dark-border rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Calculated</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $calculated->count() }}</p>
                    </div>
                    <div class="p-4 border border-neutral-200 dark:border-dark-border rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Approved</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $approved->count() }}</p>
                    </div>
                    <div class="p-4 border border-neutral-200 dark:border-dark-border rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Paid</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $paid->count() }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="mt-6 flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Actions</h3>
                </div>
                <div class="p-5 flex-1 flex flex-col gap-3">
                    @if($period->status === 'draft' && $calculated->isEmpty())
                        <form method="POST" action="{{ route('admin.payroll.periods.generate', $period) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Generate Payroll</button>
                        </form>
                    @endif
                    @if($calculated->isNotEmpty() || $approved->isNotEmpty())
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 text-center py-2">
                            Manage individual records via Payroll History
                        </div>
                    @endif
                    <a href="{{ route('admin.payroll.history') }}" class="w-full text-sm text-primary-600 dark:text-primary-400 hover:underline text-center py-2">View All Payroll Records</a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
