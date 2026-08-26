<x-layouts.app title="Settings">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item active>Settings</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Profile</h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage your profile photo and display name.</p>
    </div>

    <x-ui.card>
        <div class="p-6">
            <x-ui.toast :message="session('status')" />

            <div class="flex items-start gap-6">
                <x-profile-photo-uploader :size="'h-32 w-32'" :editable="auth()->user()->role !== 'student'" />

                @if (auth()->user()->role === 'student')
                    <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">
                        Managed by your administrator.
                    </p>
                @endif
            </div>

            @if (auth()->user()->role !== 'student')
                <form method="POST" action="{{ route('settings.profile.update') }}" class="mt-6 border-t border-neutral-200 dark:border-dark-border pt-6">
                    @csrf
                    @method('PATCH')
                    <div class="max-w-sm">
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Display Name</label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name', auth()->user()->name) }}"
                               class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus-visible-ring"
                               placeholder="Enter your display name">
                        @error('name')
                            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium text-sm focus-visible-ring transition-colors">
                            Save Display Name
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </x-ui.card>
</x-layouts.app>
