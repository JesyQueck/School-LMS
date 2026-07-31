<x-layouts.app title="My Attendance">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/student/dashboard">Student</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Attendance</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Attendance</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $student->full_name ?? 'Student' }} &middot; {{ $student->class->name ?? 'N/A' }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Attendance History</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Daily attendance records for the current term.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Term</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Class</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                        @forelse($attendance as $record)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                <td class="px-6 py-4 text-sm text-neutral-900 dark:text-white">{{ $record->date ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $record->term->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $record->class->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $status = strtolower($record->status ?? '');
                                        $statusConfig = match($status) {
                                            'present' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Present'],
                                            'absent' => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Absent'],
                                            'late' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Late'],
                                            default => ['bg' => 'bg-neutral-100 dark:bg-neutral-800', 'text' => 'text-neutral-700 dark:text-neutral-300', 'label' => ucfirst($status)],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No attendance records found.</p>
                                        <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Records will appear here once marked by your teachers.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
