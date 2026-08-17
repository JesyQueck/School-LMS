<x-layouts.app title="Subjects">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Subjects</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Subjects</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage subjects and assign them to multiple classes.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 p-4 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg">
            <p class="text-sm text-success-800 dark:text-success-200">{{ session('status') }}</p>
        </div>
    @endif

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">All Subjects</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $subjects->count() }} subjects defined.</p>
            </div>
            <a href="{{ route('admin.subjects.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
                Create Subject
            </a>
        </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Subject</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Classes</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Teachers</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($subjects as $subject)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $subject->name }}</td>
                            <td class="px-6 py-4">
                                @if($subject->classSubjects->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($subject->classSubjects as $cs)
                                            <span class="inline-flex items-center rounded-md bg-neutral-100 dark:bg-neutral-800 px-2.5 py-0.5 text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $cs->class->name ?? 'N/A' }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400 italic">No classes assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $teacherCount = $subject->classSubjects->sum(function($cs) {
                                        return $cs->teacherAssignments->count();
                                    });
                                @endphp
                                <span class="text-sm text-neutral-900 dark:text-white">{{ $teacherCount }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">Edit</a>
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Are you sure you want to delete {{ $subject->name }}? This will remove all class associations and teacher assignments.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300 font-medium">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No subjects found.</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Create your first subject below.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
