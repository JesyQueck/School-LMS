<x-layouts.app title="Announcements">
    @php
        $breadcrumbs = [
            ['label' => 'Parent', 'href' => '/parent/dashboard'],
            ['label' => 'Announcements', 'active' => true],
        ];
    @endphp

    <x-slot:title>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <x-ui.breadcrumbs>
                    @foreach($breadcrumbs as $crumb)
                        <x-ui.breadcrumb-item :href="$crumb['href'] ?? null" :active="$crumb['active'] ?? false">
                            {{ $crumb['label'] }}
                        </x-ui.breadcrumb-item>
                    @endforeach
                </x-ui.breadcrumbs>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Announcements</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Stay updated with school news and events.</p>
            </div>
        </div>
    </x-slot:title>

    <div class="grid grid-cols-1 gap-6">
        @forelse($announcements as $announcement)
            <x-ui.card>
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">General</span>
                                <span class="text-xs text-neutral-400 dark:text-neutral-500">{{ $announcement->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">{{ $announcement->title }}</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">{{ $announcement->body }}</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-3">By {{ $announcement->createdBy->name ?? 'Admin' }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="No announcements" description="There are no announcements at this time. Check back later for updates." />
        @endforelse

        @if($announcements->hasPages())
            <div class="mt-6">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
