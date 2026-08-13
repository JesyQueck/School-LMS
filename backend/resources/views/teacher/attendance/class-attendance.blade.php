<x-layouts.app title="Class Attendance">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Class Attendance</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Class Attendance</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $classAssignment->class->name ?? 'N/A' }} &middot; {{ $classAssignment->term->name ?? 'Current Term' }}</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Mark Attendance</h3>
                <form method="POST" action="{{ route('teacher.attendance.start') }}">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">
                    <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Start Attendance</button>
                </form>
            </div>
        </div>
        <div class="p-6">
            @if($showAttendanceForm)
                <form method="POST" action="{{ route('teacher.attendance.store') }}">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">
                    <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">
                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                            <thead class="bg-neutral-50 dark:bg-dark-surface">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Present</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Absent</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Late</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                @forelse($students as $student)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <input type="radio" name="status[{{ $student->id }}]" value="present" class="h-4 w-4 text-primary-600 border-neutral-300 focus:ring-primary-500" required>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input type="radio" name="status[{{ $student->id }}]" value="absent" class="h-4 w-4 text-danger-600 border-neutral-300 focus:ring-danger-500">
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input type="radio" name="status[{{ $student->id }}]" value="late" class="h-4 w-4 text-warning-600 border-neutral-300 focus:ring-warning-500">
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No students found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-success-600 hover:bg-success-700 text-white font-medium px-6 py-2 rounded-lg text-sm">Save Attendance</button>
                    </div>
                </form>
            @else
                <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">Click "Start Attendance" to begin marking attendance for today.</p>
            @endif
        </div>
    </x-ui.card>
</x-layouts.app>
