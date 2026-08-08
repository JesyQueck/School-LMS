<x-layouts.app title="My Subjects - Assignments">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/teacher/dashboard">Teacher</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/teacher/assignments" active>My Subjects</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Assignments</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Subjects - Assignments</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View and manage assignment entries.</p>
    </div>

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Assignment Entries</h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">This page is for entering assignment scores.</p>
        </div>
    </x-ui.card>
</x-layouts.app>