<x-layouts.app title="My Timetable">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Your teaching schedule.</p>
    </div>

    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timetable = $timetable->groupBy('day');
    @endphp

    <div class="space-y-6">
        @foreach($days as $day)
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $day }}</h3>
                </div>
                <div class="p-6">
                    @if($timetable->has($day))
                        <div class="space-y-3">
                            @foreach($timetable[$day] as $period)
                                <div class="flex items-center gap-4 p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                                    <div class="flex-shrink-0 text-center">
                                        <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ \Carbon\Carbon::parse($period->start_time)->format('g:i A') }}</p>
                                        <p class="text-xs text-neutral-400 dark:text-neutral-500">{{ \Carbon\Carbon::parse($period->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('g:i A') }}</p>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $period->classSubject->subject->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $period->classSubject->class->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No classes scheduled.</p>
                    @endif
                </div>
            </x-ui.card>
        @endforeach
    </div>
</x-layouts.app>
