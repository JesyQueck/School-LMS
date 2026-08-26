<x-layouts.app title="Settings">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item active>Settings</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Settings</h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage your profile photo.</p>
    </div>

    <x-ui.card>
        <div class="p-6">
            <x-ui.toast :message="session('status')" />

            <div class="flex items-start gap-6">
                <x-profile-photo-uploader :size="'h-64 w-64'" :editable="auth()->user()->role !== 'student'" />

                @if (auth()->user()->role === 'student')
                    <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">
                        Managed by your administrator.
                    </p>
                @endif
            </div>
        </div>
    </x-ui.card>
</x-layouts.app>
