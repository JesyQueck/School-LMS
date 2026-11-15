<x-layouts.app title="Salary Component Details - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.components.index') }}">Salary Components</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Component Details</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-2">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $salaryComponent->name }}</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Salary component details and profile assignments.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payroll.components.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    Back
                </a>
                <a href="{{ route('admin.payroll.components.edit', $salaryComponent) }}"
                   class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium">
                    Edit Component
                </a>
            </div>
        </div>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card elevated>
            <p class="text-xs font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Component Type</p>
            <p class="text-lg font-semibold text-neutral-900 dark:text-white mt-2">{{ ucfirst($salaryComponent->component_type) }}</p>
        </x-ui.card>

        <x-ui.card elevated>
            <p class="text-xs font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Calculation</p>
            <p class="text-lg font-semibold text-neutral-900 dark:text-white mt-2">{{ ucfirst($salaryComponent->calculation_type) }}</p>
        </x-ui.card>

        <x-ui.card elevated>
            <p class="text-xs font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Default Value</p>
            <p class="text-lg font-semibold text-neutral-900 dark:text-white mt-2">
                ₦{{ number_format((float) $salaryComponent->default_value, 2) }}
                @if($salaryComponent->calculation_type === 'percentage')
                    <span class="text-sm font-normal">%</span>
                @endif
            </p>
        </x-ui.card>

        <x-ui.card elevated>
            <p class="text-xs font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Status</p>
            <div class="mt-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $salaryComponent->is_active ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300' : 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
                    {{ $salaryComponent->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card elevated class="mb-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h2 class="font-semibold text-neutral-900 dark:text-white">Component Information</h2>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Description</p>
                <p class="text-sm text-neutral-900 dark:text-white mt-1">{{ $salaryComponent->description ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Taxable</p>
                <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ $salaryComponent->is_taxable ? 'Yes' : 'No' }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Created</p>
                <p class="text-sm text-neutral-900 dark:text-white mt-1">{{ $salaryComponent->created_at?->format('d M Y, h:i A') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Last Updated</p>
                <p class="text-sm text-neutral-900 dark:text-white mt-1">{{ $salaryComponent->updated_at?->format('d M Y, h:i A') ?? '—' }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card elevated>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-neutral-900 dark:text-white">Assigned Salary Profiles</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Employees whose salary profiles include this component.</p>
            </div>
            <span class="text-sm font-semibold text-neutral-600 dark:text-neutral-300">
                {{ $salaryComponent->profileAssignments->count() }} assignment(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Employee Number</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Assigned Value</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Profile Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border">
                    @forelse($salaryComponent->profileAssignments as $assignment)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">
                                {{ $assignment->salaryProfile?->employee?->full_name
                                    ?? $assignment->salaryProfile?->employee?->name
                                    ?? 'Employee unavailable' }}
                            </td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $assignment->salaryProfile?->employee?->employee_number ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-sm text-right text-neutral-900 dark:text-white">
                                {{ $salaryComponent->calculation_type === 'percentage' ? number_format((float) $assignment->value, 2) . '%' : '₦' . number_format((float) $assignment->value, 2) }}
                            </td>
                            <td class="px-6 py-3.5 text-center text-sm text-neutral-600 dark:text-neutral-400">
                                {{ ucfirst($assignment->salaryProfile?->status ?? 'Unavailable') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                This component has not been assigned to any salary profile yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>