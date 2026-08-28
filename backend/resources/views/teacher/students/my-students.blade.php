<x-layouts.app title="My Students">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>My Students</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Students</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            Class Teacher for {{ $classAssignment->class->name ?? 'N/A' }}.
            Showing {{ $students->count() }} students.
        </p>
    </div>

     <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Student List</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Admission No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($students as $student)
                        @php
                            $isClassTeacherForStudent = $classAssignment && $classAssignment->class_id === $student->class_id;
                            $studentRole = $isClassTeacherForStudent ? 'Class Teacher' : 'Subject Teacher';
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $student->admission_no ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $student->schoolClass->name ?? ($student->class_id ?? '') }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($student->gender ?? 'N/A') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">{{ $studentRole }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('teacher.students.show', $student) }}" class="text-primary-600 dark:text-primary-400 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6">
        <a href="{{ route('teacher.dashboard') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">← Back to Dashboard</a>
    </div>
</x-layouts.app>
