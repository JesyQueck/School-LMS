<x-layouts.app title="Students">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item href="{{ route('teacher.classes.index') }}">My Classes</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Students — {{ $class->name }}</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Students — {{ $class->name }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $students->count() }} students in this class.</p>
    </div>

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Student List</h3>
                <input type="text" id="studentSearch" placeholder="Search students..." class="rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm w-64">
            </div>
        </div>
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Admission No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</th>
                        @if(isset($isClassTeacher) && $isClassTeacher)
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($students as $student)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors student-row">
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $student->admission_no ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($student->gender ?? 'N/A') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Active</span>
                            </td>
                            @if(isset($isClassTeacher) && $isClassTeacher)
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('teacher.students.show', $student) }}" class="text-primary-600 dark:text-primary-400 hover:underline">View</a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ isset($isClassTeacher) && $isClassTeacher ? 5 : 4 }}" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @push('scripts')
        <script>
            document.getElementById('studentSearch')?.addEventListener('input', function (e) {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.student-row').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        </script>
    @endpush
</x-layouts.app>
