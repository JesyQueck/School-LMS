<x-layouts.app title="Eligible Employees - Staff Salaries">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries') }}">Staff Salary Payments</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Eligible Employees</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Eligible Employees</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View active staff and their salary information.</p>
    </div>

    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex-1 min-w-0">
            <form method="GET" action="{{ route('bursar.staff-salaries.employees') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input name="search" type="text" placeholder="Search by employee number..." value="{{ $filters['search'] ?? '' }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm text-sm transition-colors">Search</button>
                <a href="{{ route('bursar.staff-salaries.employees') }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm px-2 py-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Reset</a>
            </form>
        </div>
    </div>

    <x-ui.card class="mb-6" elevated>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Employee No.</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Designation</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Basic Salary</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($employees as $employee)
                        @php
                            $profile = $employee->salaryProfiles()->where('is_active', true)->orderByDesc('effective_from')->first();
                            $basic = $profile?->basic_salary ?? 0;
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $employee->employee_number }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $employee->user?->name ?? 'No user linked' }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $employee->department ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $employee->designation ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($basic, 2) }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('bursar.staff-salaries.periods') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Select Period</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No eligible employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-dark-border">
            {{ $employees->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>
