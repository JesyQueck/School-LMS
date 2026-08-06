<x-layouts.app title="Teacher Assignments">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Assignments</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Teacher Assignments</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Assign teachers to classes and subjects.</p>
    </div>

    <div class="mb-6 flex gap-3">
        <button type="button" onclick="document.getElementById('subject-assignment-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Subject Assignment
        </button>
        <button type="button" onclick="document.getElementById('class-assignment-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-800 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m3-3H9"/></svg>
            Class Teacher Assignment
        </button>
    </div>

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Subject Assignments</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $assignments->count() }} subject assignments.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Teacher</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Class</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Subject</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($assignments as $assignment)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->teacher->user->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->classSubject->class->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->classSubject->subject->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($assignment->is_active)
                                    <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-2.5 py-0.5 text-xs font-medium text-neutral-700 dark:text-neutral-300">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <form action="{{ route('admin.assignments.destroy', $assignment->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this assignment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300 text-sm font-medium">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No subject assignments found.</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Create your first subject assignment using the button above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @if($classAssignments->count())
    <x-ui.card class="mt-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Class Teacher Assignments</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $classAssignments->count() }} class teacher assignment{{ $classAssignments->count() !== 1 ? 's' : '' }}.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Teacher</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Class</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Session</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Term</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @foreach($classAssignments as $assignment)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->teacher->user->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->class->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->academicSession->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->term?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <form action="{{ route('admin.class-assignments.destroy', $assignment->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this class assignment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300 text-sm font-medium">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

    <!-- Subject Assignment Modal -->
    <div id="subject-assignment-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-premium-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Assign Subject</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Assign a teacher to teach a subject to a class.</p>
            </div>
            <form method="POST" action="{{ route('admin.assignments.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="teacher_id_sa" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Teacher <span class="text-danger-500">*</span></label>
                    <select id="teacher_id_sa" name="teacher_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Select teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? 'Unknown' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="class_subject_id_sa" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class Subject <span class="text-danger-500">*</span></label>
                    <select id="class_subject_id_sa" name="class_subject_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Select class-subject</option>
                        @foreach($classSubjects as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->class->name ?? 'Unknown' }} - {{ $cs->subject->name ?? 'Unknown' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input id="is_active_sa" name="is_active" type="checkbox" value="1" checked class="h-4 w-4 rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
                    <label for="is_active_sa" class="text-sm text-neutral-700 dark:text-neutral-300">Active assignment</label>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" onclick="document.getElementById('subject-assignment-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Assign Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Class Teacher Assignment Modal -->
    <div id="class-assignment-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-premium-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Assign Class Teacher</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Assign a teacher as Class Teacher for a class period.</p>
            </div>
            <form method="POST" action="{{ route('admin.class-assignments.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="teacher_id_ct" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Teacher <span class="text-danger-500">*</span></label>
                    <select id="teacher_id_ct" name="teacher_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Select teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? 'Unknown' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="class_id_ct" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class <span class="text-danger-500">*</span></label>
                    <select id="class_id_ct" name="class_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="academic_session_id_ct" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Academic Session <span class="text-danger-500">*</span></label>
                    <select id="academic_session_id_ct" name="academic_session_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Select session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }} @if($session->is_current) (Current)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" onclick="document.getElementById('class-assignment-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Assign Class Teacher</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>