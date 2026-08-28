<x-layouts.app title="Timetable">
    <div class="mb-3">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/parent/dashboard">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-xl font-bold text-neutral-900 dark:text-white mt-1">My Children's Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Select a child to view their weekly class schedule.</p>
    </div>

    @if($children && $children->count() > 0)
        <div class="mb-3 max-w-xs">
            <label for="child-select" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Child</label>
            <select id="child-select" class="w-full rounded-lg border border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface px-3 py-1.5 text-sm text-neutral-900 dark:text-white" onchange="window.location.href = '?student=' + this.value">
                @foreach($children as $child)
                    <option value="{{ $child->id }}" {{ $selected && $selected->id === $child->id ? 'selected' : '' }}>
                        {{ $child->full_name ?? $child->admission_no }} - {{ $child->schoolClass->name ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($selected)
            <div class="mb-2">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Showing timetable for <span class="font-medium text-neutral-900 dark:text-white">{{ $selected->full_name }}</span>
                    &middot; {{ $selected->schoolClass->name ?? 'N/A' }}
                </p>
            </div>
        @endif

        @php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $timetableByDay = $timetable->groupBy('day');
        @endphp

        @if($periods->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse border border-neutral-300 dark:border-dark-border">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-2.5 py-2 text-center font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-r border-neutral-300 dark:border-dark-border">Period</th>
                            <th class="px-3 py-2 text-left font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-r border-neutral-300 dark:border-dark-border">Time</th>
                            @foreach($days as $day)
                                <th class="px-2.5 py-2 text-center font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-r border-neutral-300 dark:border-dark-border last:border-r-0">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-300 dark:divide-dark-border">
                        @php
                            $periodCounter = 0;
                        @endphp
                        @foreach($periods as $period)
                            @php
                                $startTime = \Carbon\Carbon::parse($period->start_time)->format('g:ia');
                                $endTime = \Carbon\Carbon::parse($period->end_time)->format('g:ia');
                                $periodStart = \Carbon\Carbon::parse($period->start_time)->format('H:i');
                                $periodEnd = \Carbon\Carbon::parse($period->end_time)->format('H:i');
                                if (! $period->is_break) {
                                    $periodCounter++;
                                }
                            @endphp
                            <tr>
                                <td class="px-2.5 py-2 text-center text-neutral-900 dark:text-white font-medium border-r border-neutral-200 dark:border-dark-border">{{ $period->is_break ? 'Break' : $periodCounter }}</td>
                                <td class="px-3 py-2 text-neutral-500 dark:text-neutral-400 border-r border-neutral-200 dark:border-dark-border">{{ $startTime }} - {{ $endTime }}</td>
                                @foreach($days as $day)
                                    @php
                                        $dayEntries = $timetableByDay->get($day, collect());
                                        $entry = $dayEntries->first(function ($e) use ($periodStart, $periodEnd) {
                                            return \Carbon\Carbon::parse($e->start_time)->format('H:i') === $periodStart
                                                && \Carbon\Carbon::parse($e->end_time)->format('H:i') === $periodEnd;
                                        });
                                    @endphp
                                    <td class="px-2.5 py-2 text-center align-top border-r border-neutral-200 dark:border-dark-border last:border-r-0 min-w-[90px]">
                                        @if($entry)
                                            <div class="space-y-0.5">
                                                <div class="font-medium text-neutral-900 dark:text-white">{{ $entry->classSubject->subject->name ?? 'N/A' }}</div>
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
            <x-ui.empty-state title="No timetable available" description="There are no subjects scheduled for the selected child's class yet." />
        @endif
    @else
        <x-ui.empty-state title="No children linked" description="Contact the school administrator to link students to your account." />
    @endif
</x-layouts.app>
