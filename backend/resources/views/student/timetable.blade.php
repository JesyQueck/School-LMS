<x-layouts.app title="Timetable">

    <x-ui.breadcrumbs class="mb-2">
        <x-ui.breadcrumb-item href="/student/dashboard">Student</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-3">
        <h1 class="text-xl font-bold text-neutral-900 dark:text-white">
            My Timetable
        </h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
            Weekly class schedule.
        </p>
    </div>

    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timetableByDay = $timetable->groupBy('day');
    @endphp

    @if($periods->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse border border-neutral-300 dark:border-dark-border">
                <thead class="bg-neutral-50 dark:bg-neutral-800">
                    <tr>
                        <th class="px-2.5 py-2 text-center font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-r border-neutral-300 dark:border-dark-border">Day</th>
                        @foreach($periods as $period)
                            @php
                                $startTime = \Carbon\Carbon::parse($period->start_time)->format('g:ia');
                                $endTime = \Carbon\Carbon::parse($period->end_time)->format('g:ia');
                                $periodStart = \Carbon\Carbon::parse($period->start_time)->format('H:i');
                                $periodEnd = \Carbon\Carbon::parse($period->end_time)->format('H:i');
                            @endphp
                            <th class="px-2.5 py-2 text-center font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-r border-neutral-300 dark:border-dark-border last:border-r-0">{{ $period->is_break ? 'Break' : $period->name ?? 'Period ' . $period->period_number }}<br><span class="text-neutral-600 dark:text-neutral-400 text-[10px]">{{ $startTime }} - {{ $endTime }}</span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-300 dark:divide-dark-border">
                    @foreach($days as $day)
                        @php
                            $dayEntries = $timetableByDay->get($day, collect());
                        @endphp
                        <tr>
                            <td class="px-2.5 py-2 text-center text-neutral-900 dark:text-white font-medium border-r border-neutral-200 dark:border-dark-border">{{ $day }}</td>
                            @foreach($periods as $period)
                                @php
                                    $periodStart = \Carbon\Carbon::parse($period->start_time)->format('H:i');
                                    $periodEnd = \Carbon\Carbon::parse($period->end_time)->format('H:i');
                                    $entry = $dayEntries->first(function ($e) use ($periodStart, $periodEnd) {
                                        return \Carbon\Carbon::parse($e->start_time)->format('H:i') === $periodStart
                                            && \Carbon\Carbon::parse($e->end_time)->format('H:i') === $periodEnd;
                                    });
                                @endphp
                                <td class="px-2.5 py-2 text-center align-top border-r border-neutral-200 dark:border-dark-border last:border-r-0 min-w-[90px]">
                                    @if($entry)
                                        <div class="space-y-0.5">
                                            <div class="font-medium text-neutral-900 dark:text-white">{{ $entry->period_name_override ?? ($entry->classSubject->subject->name ?? 'N/A') }}</div>
                                            <div class="text-neutral-500 dark:text-neutral-400">{{ $entry->teacher?->user?->name ?? 'Not assigned' }}</div>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-6">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">No timetable entries found for your class.</p>
        </div>
    @endif

    <div class="mt-3">
        <a
            href="{{ route('student.dashboard') }}"
            class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
        >
            &larr; Back to Dashboard
        </a>
    </div>

</x-layouts.app>
