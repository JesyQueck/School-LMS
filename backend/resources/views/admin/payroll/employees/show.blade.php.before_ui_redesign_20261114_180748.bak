<x-layouts.app title="Employee Details - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.employees.index') }}">Employees</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Employee Details</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Employee Details</h1>
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
            <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ $employee->employee_number }}</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $employee->user?->name ?? 'No user linked' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payroll.employees.edit', $employee) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Edit</a>
            <a href="{{ route('admin.payroll.employees.index') }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm font-medium px-4 py-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <div class="lg:col-span-3">
            <x-ui.card class="mb-6" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Employment Details</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Employee Number</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $employee->employee_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Employment Type</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ ucfirst($employee->employment_type) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Employment Status</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $employee->employment_status)) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Department</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $employee->department ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Designation</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $employee->designation ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Date Employed</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $employee->date_employed?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Date Left</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $employee->date_left?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Linked User</p>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $employee->user?->name ?? 'None' }} ({{ $employee->user?->email ?? '—' }})</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Salary Profiles</h3>
                </div>
                <div class="p-5">
                    @forelse($employee->salaryProfiles as $profile)
                        <div class="p-4 border border-neutral-200 dark:border-dark-border rounded-xl mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ $profile->basic_salary ? '₦'.number_format($profile->basic_salary, 2) : '₦0.00' }}</span>
                                <span class="text-xs {{ $profile->is_active ? 'bg-success-100 text-success-700' : 'bg-neutral-100 text-neutral-700' }} dark:{{ $profile->is_active ? 'bg-success-900/30 text-success-300' : 'bg-neutral-800/30 text-neutral-300' }} px-2 py-0.5 rounded">{{ $profile->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Effective: {{ $profile->effective_from->format('d M Y') }}{{ $profile->effective_to ? ' to '.$profile->effective_to->format('d M Y') : ' — Present' }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ $profile->components->count() }} component(s) assigned</p>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-6">No salary profiles assigned.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Payroll Records</h3>
                </div>
                <div class="p-5 flex-1 overflow-y-auto">
                    @forelse($employee->payrollRecords->sortDesc() as $record)
                        <div class="p-3 border border-neutral-200 dark:border-dark-border rounded-lg mb-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ $record->period->name }}</span>
                                <span class="text-xs {{ $record->status === 'paid' ? 'bg-success-100 text-success-700' : ($record->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-neutral-100 text-neutral-700') }} dark:{{ $record->status === 'paid' ? 'bg-success-900/30 text-success-300' : ($record->status === 'approved' ? 'bg-blue-900/30 text-blue-300' : 'bg-neutral-800/30 text-neutral-300') }} px-2 py-0.5 rounded">{{ ucfirst($record->status) }}</span>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Net Pay: <span class="text-sm font-medium text-neutral-900 dark:text-white">₦{{ number_format($record->net_pay, 2) }}</span></p>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-6">No payroll records.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
