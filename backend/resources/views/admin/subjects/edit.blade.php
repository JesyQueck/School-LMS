<x-layouts.app title="Edit Subject">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.subjects.index') }}">Subjects</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Edit Subject</x-ui-breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Edit Subject</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Update subject details and class assignments.</p>
    </div>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Subject Name <span class="text-danger-500">*</span></label>
                <input name="name" type="text" placeholder="e.g. Mathematics" required value="{{ old('name', $subject->name) }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base focus:outline-none focus:ring-1 focus:ring-primary-500">
                @error('name')
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Classes</label>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">Select which classes this subject is offered to. Checkboxes replace the current selection.</p>
                <div class="flex flex-wrap gap-3">
                    @php
                        $assignedClassIds = $subject->classSubjects->pluck('class_id')->toArray();
                    @endphp
                    @foreach($classes as $class)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, old('class_ids', $assignedClassIds)) ? 'checked' : '' }} class="h-4 w-4 rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $class->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('class_ids')
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('admin.subjects.index') }}" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Save Changes</button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
