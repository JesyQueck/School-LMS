<x-layouts.app title="Assignments">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('student.dashboard') }}">Dashboard</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Assignments</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
    </div>

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Assignments</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Pending and completed assignments.</p>
    </div>

    <x-ui.card>
        <div class="p-6 text-center py-8">
            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7M5 19h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Assignments system coming soon.</p>
        </div>
    </x-ui.card>
</x-layouts.app>