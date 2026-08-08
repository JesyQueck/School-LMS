<x-layouts.app title="Messages">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('parent.dashboard') }}">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Messages</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
    </div>

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Messages</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Communicate with teachers.</p>
    </div>

    <x-ui.card>
        <div class="p-6 text-center py-8">
            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M9 16L7 14M9 16l2 2M9 16l2-2M16 10l2 2M16 10l2-2M16 10l-2 2m-8 0h8"/></svg>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Messaging system coming soon.</p>
        </div>
    </x-ui.card>
</x-layouts.app>