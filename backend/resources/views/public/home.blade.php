<x-layouts.guest>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-neutral-900 dark:text-white mb-4">Welcome to Our School</h1>
            <p class="text-lg text-neutral-600 dark:text-neutral-400 max-w-2xl mx-auto">Providing quality education with excellence in academics and character. Building tomorrow's leaders today.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center">
                <div class="h-12 w-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Academic Excellence</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Comprehensive curriculum designed to foster critical thinking and creativity.</p>
            </div>
            <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center">
                <div class="h-12 w-12 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Expert Faculty</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Dedicated teachers committed to nurturing every student's potential.</p>
            </div>
            <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center">
                <div class="h-12 w-12 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Holistic Development</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Focus on academics, sports, arts, and character building.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm p-8">
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6 text-center">Latest Announcements</h2>
            @forelse($announcements as $announcement)
                <div class="py-4 border-b border-neutral-100 dark:border-neutral-800 last:border-b-0">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $announcement->title }}</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $announcement->body }}</p>
                </div>
            @empty
                <p class="text-center text-neutral-500 dark:text-neutral-400">No announcements at this time.</p>
            @endforelse
        </div>
    </div>
</x-layouts.guest>
