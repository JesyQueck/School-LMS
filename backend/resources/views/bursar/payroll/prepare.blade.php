<x-layouts.app title="Prepare Payroll - Staff Salaries">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries') }}">Staff Salary Payments</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries.periods') }}">Payroll Periods</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Prepare Payroll</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Prepare Payroll</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Calculate payroll for {{ $period->name }}. Employees already having a record for this period are excluded.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif
    @if(session('excluded'))
        <x-ui.alert variant="warning" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1 text-sm font-medium text-warning-800 dark:text-warning-300">
                    {{ count(session('excluded')) }} employee(s) excluded from payroll generation.
                </div>
            </div>
        </x-ui.alert>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-neutral-900 dark:text-white">{{ $period->name }}</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $period->start_date->format('d M Y') }} &mdash; {{ $period->end_date->format('d M Y') }} &middot; Pay date: {{ $period->pay_date->format('d M Y') }}</p>
        </div>
        <form method="POST" action="{{ route('bursar.staff-salaries.generate', $period) }}" class="inline">
            @csrf
            <button type="submit" class="bg-success-600 hover:bg-success-700 text-white font-medium px-6 py-2.5 rounded-lg shadow-sm text-sm transition-colors flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 7l5-5m0 0l-5 5m5-5L7 12"/></svg>
                Calculate Payroll
            </button>
        </form>
    </div>

    <x-ui.card class="mb-6" elevated>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Employees to Process ({{ $employees->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Employee No.</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Name</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Basic Salary</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Estimated Net</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Eligible</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($employees as $employee)
                        @php
                            $profile = $employee->salaryProfiles()->where('is_active', true)->orderByDesc('effective_from')->first();
                            $eligible = (bool) $profile;
                            $basic = $profile?->basic_salary ?? 0;
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $employee->employee_number }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $employee->user?->name ?? 'No user linked' }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($basic, 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">{{ $eligible ? '₦'.number_format($basic, 2) : '—' }}</td>
                            <td class="px-6 py-3.5 text-left">
                                <span class="inline-flex items-center rounded-full {{ $eligible ? 'bg-success-100 dark:bg-success-900/30' : 'bg-neutral-100 dark:bg-neutral-800/30' }} px-2 py-0.5 text-xs font-medium {{ $eligible ? 'text-success-700 dark:text-success-300' : 'text-neutral-700 dark:text-neutral-300' }}">{{ $eligible ? 'Eligible' : 'No Active Profile' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No employees to process.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
