<x-layouts.app title="Edit Timetable Entry">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="/admin">Admin</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item href="{{ route('admin.timetable.index') }}">Timetable</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Edit Entry</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Edit Timetable Entry</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Update this schedule entry.</p>
    </div>

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Entry Details</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.timetable.update', $timetable) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class & Subject <span class="text-danger-500">*</span></label>
                    <select name="class_subject_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        <option value="">Select class & subject</option>
                        @foreach($classes as $class)
                            <optgroup label="{{ $class->name }}">
                                @foreach($class->classSubjects as $cs)
                                    <option value="{{ $cs->id }}" {{ old('class_subject_id', $timetable->class_subject_id) == $cs->id ? 'selected' : '' }}>{{ $cs->subject->name ?? 'Unknown Subject' }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('class_subject_id')
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Day <span class="text-danger-500">*</span></label>
                    <select name="day" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        @foreach(\App\Http\Controllers\Admin\TimetableController::DAYS as $day)
                            <option value="{{ $day }}" {{ old('day', $timetable->day) == $day ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>
                    @error('day')
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Start Time <span class="text-danger-500">*</span></label>
                    <input type="time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($timetable->start_time)->format('H:i')) }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                    @error('start_time')
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">End Time <span class="text-danger-500">*</span></label>
                    <input type="time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($timetable->end_time)->format('H:i')) }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                    @error('end_time')
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Teacher</label>
                    <select name="teacher_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">Not assigned</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $timetable->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->user?->name ?? 'Unnamed Teacher' }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Session</label>
                    <select name="academic_session_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">Default (current)</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id', $timetable->academic_session_id) == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </select>
                    @error('academic_session_id')
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Term</label>
                    <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">Default (current)</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ old('term_id', $timetable->term_id) == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                        @endforeach
                    </select>
                    @error('term_id')
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                    <a href="{{ route('admin.timetable.index') }}" class="bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 font-medium px-4 py-2 rounded-lg text-sm">Cancel</a>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </x-ui.card>
</x-layouts.app>
