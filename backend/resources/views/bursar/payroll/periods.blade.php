<x-layouts.app title="Payroll Periods - Staff Salaries">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.staff-salaries') }}">Staff Salary Payments</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Payroll Periods</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payroll Periods</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View available payroll periods and prepare payroll.</p>
    </div>

    <x-ui.card class="mb-6" elevated>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Period Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Pay Date</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($periods as $period)
                        @php
                            $statusConfig = match($period->status) {
                                'draft' => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300'],
                                'processed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300'],
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300'],
                                'locked' => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300'],
                                default => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300'],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $period->name }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $period->start_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $period->end_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $period->pay_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ ucfirst($period->status) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                @if(in_array($period->status, ['draft', 'processed']))
                                    <a href="{{ route('bursar.staff-salaries.prepare', $period) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Prepare Payroll</a>
                                @else
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payroll periods found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-dark-border">
            {{ $periods->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>
