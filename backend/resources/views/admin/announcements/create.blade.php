<x-layouts.app title="Create Announcement">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.announcements') }}">Announcements</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>New Announcement</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">New Announcement</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Create a new announcement for students, teachers, or all users.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-6">
        @csrf

        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcement Details</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Title <span class="text-danger-500">*</span></label>
                    <input id="title" name="title" type="text" required value="{{ old('title') }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="e.g. School Holiday Notice">
                    @error('title')
                        <p class="text-sm text-danger-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Message <span class="text-danger-500">*</span></label>
                    <textarea id="body" name="body" rows="6" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Write your announcement message here.">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-sm text-danger-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target_role" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Target Audience <span class="text-danger-500">*</span></label>
                    <select id="target_role" name="target_role" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 appearance-none">
                        <option value="">Select audience</option>
                        <option value="all" {{ old('target_role') == 'all' ? 'selected' : '' }}>All Users (Students, Teachers & Parents)</option>
                        <option value="student" {{ old('target_role') == 'student' ? 'selected' : '' }}>Students Only</option>
                        <option value="teacher" {{ old('target_role') == 'teacher' ? 'selected' : '' }}>Teachers Only</option>
                        <option value="parent" {{ old('target_role') == 'parent' ? 'selected' : '' }}>Parents Only</option>
                    </select>
                    @error('target_role')
                        <p class="text-sm text-danger-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('admin.announcements') }}" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-sm">Cancel</a>
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Publish Announcement</button>
        </div>
    </form>
</x-layouts.app>
