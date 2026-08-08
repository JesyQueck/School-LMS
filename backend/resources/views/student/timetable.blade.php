<x-layouts.app title="Timetable">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('student.dashboard') }}">Dashboard</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
    </div>

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Weekly class schedule</p>
    </div>

    <x-ui.card>
        <div class="p-6">
            @if($student && $student->class && $student->class->classSubjects)
                <table class="w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Day</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Teacher</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-200 dark:divide-neutral-800">
                        @forelse($student->class->classSubjects as $classSubject)
                            @foreach($classSubject->timetable ?? [] as $t)
                            <tr>
                                <td class="px-4 py-3 text-sm text-neutral-900 dark:text-white">{{ $t->day ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-neutral-500 dark:text-neutral-400">{{ $t->start_time ?? '' }} - {{ $t->end_time ?? '' }}</td>
                                <td class="px-4 py-3 text-sm text-neutral-900 dark:text-white">{{ $t->subject->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 text-sm text-neutral-500 dark:text-neutral-400">{{ $t->teacher->name ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-neutral-500 dark:text-neutral-400">No timetable data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-8">No class assigned.</p>
            @endif
        </div>
    </x-ui.card>
</x-layouts.app>