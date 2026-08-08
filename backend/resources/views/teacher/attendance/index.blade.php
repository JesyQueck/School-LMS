<x-layouts.app title="Attendance">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Attendance</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">Mark attendance for your class</p>
    </div>

    @if($classAssignment)
    @if($showAttendanceForm)
    <x-ui.card>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">
                Class: {{ $classAssignment->class->name ?? 'N/A' }}
                @if($classAssignment->term)
                    ({{ $classAssignment->term->name }})
                @endif
            </h3>
        </div>

        <form id="attendance-form" method="POST" action="{{ route('teacher.attendance.store') }}">
            @csrf
            <div class="px-6 pb-5">
                <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">
                <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">

                <table class="w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Present</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Absent</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-200 dark:divide-neutral-800">
                        @foreach($students as $student)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                                {{ $student->full_name ?? $student->name ?? $student->admission_no }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                                <input type="radio" name="status[{{ $student->id }}]" value="present" class="mr-2" checked>
                                <label class="inline-flex items-center cursor-pointer">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Yes</span>
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="radio" name="status[{{ $student->id }}]" value="absent" class="mr-2">
                                <label class="inline-flex items-center cursor-pointer">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-400">No</span>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-6 px-6 pb-5">
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                        Save Attendance
                    </button>
                </div>
            </div>
        </form>
    </x-ui.card>
    @else
    <x-ui.card>
        <div class="p-6 text-center">
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">Start Attendance for Today</h3>
            <p class="text-neutral-500 dark:text-neutral-400 mb-4">Date: {{ now()->toDateString() }}</p>
            <form method="POST" action="{{ route('teacher.attendance.start') }}">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">
                <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">
                <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                    Start Attendance
                </button>
            </form>
        </div>
    </x-ui.card>
    @endif
    @else
    <x-ui.card>
        <div class="p-6 text-center">
            <p class="text-neutral-500 dark:text-neutral-400">No class assignment found for the current term.</p>
        </div>
    </x-ui.card>
    @endif
</x-layouts.app>