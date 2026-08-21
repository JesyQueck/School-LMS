<x-layouts.app title="Timetable">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="/admin">Admin</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View and manage class schedules. Filter by class or teacher.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card class="mb-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filters</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('admin.timetable.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
                @if($currentTerm)
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Session</label>
                    <select name="session_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">All Sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ ($request->input('session_id', $currentTerm?->academic_session_id) ?? '') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Term</label>
                    <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">All Terms</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ ($request->input('term_id', $currentTerm?->id) ?? '') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                    <div class="sm:col-span-2">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No current term configured. Please set up an academic session and term first.</p>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class</label>
                    <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $request->input('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Teacher</label>
                    <select name="teacher_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $request->input('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->user?->name ?? 'Unnamed Teacher' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm">Filter</button>
                    <a href="{{ route('admin.timetable.index') }}" class="flex-1 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 font-medium px-4 py-2 rounded-lg text-center">Clear</a>
                </div>
            </form>
        </div>
    </x-ui.card>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.timetable.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Entry
        </a>
    </div>

    <div class="space-y-6">
        @php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $grouped = $timetable->groupBy('day');
        @endphp

        @if($timetable->isEmpty())
            <x-ui.card>
                <div class="p-6">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center">No timetable entries found. {{ $currentTerm ? '' : 'Create a session and term first, then ' >}}Click "Add Entry" to create your first schedule.</p>
                </div>
            </x-ui.card>
        @else
            @foreach($days as $day)
                @if($grouped->has($day))
                    <x-ui.card>
                        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $day }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                                <thead class="bg-neutral-50 dark:bg-neutral-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Time</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Subject</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Teacher</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-200 dark:divide-dark-border">
                                    @foreach($grouped[$day]->sortBy('start_time') as $entry)
                                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                                {{ \Carbon\Carbon::parse($entry->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($entry->end_time)->format('H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">
                                                {{ $entry->classSubject->subject->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                                {{ $entry->classSubject->class->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                                {{ $entry->teacher?->user?->name ?? 'Not assigned' }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('admin.timetable.edit', $entry) }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 text-sm font-medium">Edit</a>
                                                    <form method="POST" action="{{ route('admin.timetable.destroy', $entry) }}" onsubmit="return confirm('Are you sure you want to delete this timetable entry?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-danger-600 dark:text-danger-400 hover:text-danger-700 dark:hover:text-danger-300 text-sm font-medium">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card>
                @endif
            @endforeach
        @endif
    </div>
</x-layouts.app>
