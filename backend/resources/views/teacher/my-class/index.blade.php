<x-layouts.app title="My Class">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">My Class</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">View your class information and student details</p>
    </div>

    @if($classAssignment && $students->count() > 0)
    <div class="space-y-6">
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
                <table class="w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Admission No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Attendance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-200 dark:divide-neutral-800">
                        @foreach($students as $student)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                                {{ $student->full_name ?? $student->name ?? $student->admission_no }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $student->admission_no ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $student->gender ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $student->status ?? 'Active' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                @if(isset($student->attendances) && $student->attendances->count() > 0)
                                    {{ $student->attendances->where('status', 'present')->count() }}/{{ $student->attendances->count() }} days
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
    @else
    <x-ui.card>
        <div class="p-6 text-center">
            <p class="text-neutral-500 dark:text-neutral-400">No students found in this class.</p>
        </div>
    </x-ui.card>
    @endif
</x-layouts.app>