<x-layouts.app title="Timetable">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/parent/dashboard">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Weekly Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View the schedule for your children's classes.</p>
    </div>

    @if($children && $children->count() > 0)
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Children:</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($children as $child)
            <span class="text-xs px-3 py-1 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                {{ $child->full_name ?? $child->admission_no }} - {{ $child->class->name ?? 'N/A' }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

    <div class="space-y-4">
        @foreach($timetableSlots as $daySchedule)
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $daySchedule['day'] }}</h3>
            </div>
            <div class="p-4">
                <table class="w-full">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-neutral-800">
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Time</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Subject</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Teacher</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-surface">
                        @foreach($daySchedule['periods'] as $period)
                        <tr>
                            <td class="px-4 py-2 text-sm text-neutral-900 dark:text-white">{{ $period['time'] }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $period['subject'] }}</td>
                            <td class="px-4 py-2 text-sm text-neutral-500 dark:text-neutral-400">{{ $period['teacher'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @endforeach
    </div>
</x-layouts.app>