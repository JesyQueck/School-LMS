<x-layouts.app title="Edit Class">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.classes') }}">Classes</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Edit Class</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Edit Class</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Update the class name and assign a form teacher.</p>
    </div>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.classes.update', $class) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class Name <span class="text-danger-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ $class->name }}" required
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('name')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="form_teacher_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Form Teacher</label>
                <select id="form_teacher_id" name="form_teacher_id"
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 appearance-none">
                    <option value="">None</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ ($class->form_teacher_id == $teacher->id) ? 'selected' : '' }}>{{ $teacher->user->name ?? 'Unknown' }}</option>
                    @endforeach
                </select>
                @error('form_teacher_id')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('admin.classes') }}"
                    class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</a>
                <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Save Changes</button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
