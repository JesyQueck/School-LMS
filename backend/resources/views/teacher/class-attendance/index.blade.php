<x-layouts.app title="Class Attendance">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Class Attendance</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">Mark attendance for your class</p>
    </div>

    @if($classAssignment)
    <x-ui.card>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">
                Class: {{ $classAssignment->class->name ?? 'N/A' }}
                @if($classAssignment->term)
                    ({{ $classAssignment->term->name }})
                @endif
            </h3>
        </div>
        <div class="p-6">
            @if($students->count() > 0)
            <form id="attendance-form">
                @csrf
                <table class="w-full divide-y divide-neutral-200 dark:divide-neutral-700">
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
                                {{ $student->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">
                                <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">
                                <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="status[{{ $student->id }}]" value="present" class="mr-2">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Yes</span>
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="status[{{ $student->id }}]" value="absent" class="mr-2" checked>
                                    <span class="text-sm text-neutral-600 dark:text-neutral-400">No</span>
                                </label>
                             </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-6 flex gap-4">
                    <button type="submit" form="attendance-form" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                        Save Attendance
                    </button>
                </div>
            </form>
            @else
                <p class="text-center py-8 text-neutral-500 dark:text-neutral-400">No students found in this class.</p>
            @endif
        </div>
    </x-ui.card>
    @else
        <x-ui.card>
            <div class="p-6 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">No class assignment found for the current term.</p>
            </div>
        </x-ui.card>
    @endif
</x-layouts.app>