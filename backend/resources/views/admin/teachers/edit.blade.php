<x-layouts.app title="Edit Teacher">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.teachers') }}">Teachers</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Edit Teacher</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Edit Teacher</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Update {{ $teacher->user->name ?? 'this teacher' }}'s details.</p>
    </div>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Full Name <span class="text-danger-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $teacher->user->name ?? '') }}" required
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('name')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email Address <span class="text-danger-500">*</span></label>
                <input id="email" name="email" type="email" value="{{ old('email', $teacher->user->email ?? '') }}" required
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('email')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Phone Number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $teacher->user->phone ?? '') }}"
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base placeholder-neutral-400 dark:placeholder-neutral-400 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="e.g. 08012345678">
                @error('phone')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="qualification" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Qualification</label>
                <input id="qualification" name="qualification" type="text" value="{{ old('qualification', $teacher->qualification ?? '') }}"
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base placeholder-neutral-400 dark:placeholder-neutral-400 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="e.g. B.Ed Mathematics">
                @error('qualification')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Password</label>
                <input id="password" name="password" type="password"
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base placeholder-neutral-400 dark:placeholder-neutral-400 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Leave blank to keep current password">
                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Must be at least 8 characters with mixed case, a number, and a symbol. Leave blank to keep the current password.</p>
                @error('password')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('admin.teachers') }}"
                    class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</a>
                <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Save Changes</button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
