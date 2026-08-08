<x-layouts.app title="Timetable">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/student/dashboard">Student</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Weekly class schedule.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 mb-6">
        <div class="sm:col-span-2">
            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Class Info</h3>
                </div>
                <div class="p-6 space-y-2">
                    <p class="text-sm"><span class="text-neutral-500 dark:text-neutral-400">Name:</span> <span class="text-neutral-900 dark:text-white">{{ $student->full_name ?? '---' }}</span></p>
                    <p class="text-sm"><span class="text-neutral-500 dark:text-neutral-400">Class:</span> <span class="text-neutral-900 dark:text-white">{{ $student->class->name ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-neutral-500 dark:text-neutral-400">Admission No:</span> <span class="text-neutral-900 dark:text-white">{{ $student->admission_no ?? 'N/A' }}</span></p>
                </div>
            </x-ui.card>
        </div>
        <div class="sm:col-span-3">
            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Today's Classes</h3>
                </div>
                <div class="p-6">
                    @php
                        $todaySchedule = $periods->filter(fn($p) => strtolower($p['day']) === strtolower(now()->format('l')));
                    @endphp
                    @if($todaySchedule->count() > 0)
                    <div class="space-y-3">
                        @foreach($todaySchedule as $period)
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                            <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-medium text-xs">
                                {{ $period['period'] }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $period['subject'] }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">with {{ $period['teacher'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No classes scheduled for today.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    <div class="space-y-4">
        @php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        @endphp
        @foreach($days as $day)
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $day }}</h3>
            </div>
            <div class="p-4">
                <table class="w-full">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Period</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Subject</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Teacher</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-surface">
                        @php
                            $dayClasses = $periods->filter(fn($p) => $p['day'] === $day);
                        @endphp
                        @foreach($dayClasses as $p)
                        <tr>
                            <td class="px-4 py-2 text-sm text-neutral-900 dark:text-white">{{ $p['period'] }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $p['subject'] }}</td>
                            <td class="px-4 py-2 text-sm text-neutral-500 dark:text-neutral-400">{{ $p['teacher'] }}</td>
                        </tr>
                        @endforeach
                        @if($dayClasses->count() === 0)
                        <tr><td colspan="3" class="px-4 py-2 text-sm text-neutral-500 dark:text-neutral-400 text-center">No classes</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @endforeach
    </div>

    <div class="mt-4">
        <a href="{{ route('student.dashboard') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
            ← Back to Dashboard
        </a>
    </div>
</x-layouts.app>