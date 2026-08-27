<x-layouts.guest>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-neutral-900 dark:text-white mb-4">Announcements</h1>
            <p class="text-lg text-neutral-600 dark:text-neutral-400">Stay updated with the latest news and events from our school.</p>
        </div>

        <div class="space-y-6">
            @forelse($announcements as $announcement)
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $announcement->title }}</h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">By {{ $announcement->createdBy->name ?? 'Admin' }} &middot; {{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">General</span>
                    </div>
                    <p class="mt-3 text-neutral-600 dark:text-neutral-400">{{ $announcement->body }}</p>
                    @if ($announcement->image)
                        <div class="mt-4">
                            <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border object-cover">
                        </div>
                    @endif
                </div>
            @empty
                <x-ui.empty-state title="No announcements" description="There are no announcements at this time." />
            @endforelse
        </div>

        @if($announcements->hasPages())
            <div class="mt-8">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</x-layouts.guest>
