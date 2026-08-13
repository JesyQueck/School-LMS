<x-layouts.app title="Announcements">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Announcements</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Announcements</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Latest announcements for teachers.</p>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $announcement)
            <x-ui.card>
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-white">{{ $announcement->title }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $announcement->body }}</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-2">{{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="No announcements" description="There are no announcements at this time." />
        @endforelse
    </div>
</x-layouts.app>
