<x-layouts.app title="My Children">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('parent.dashboard') }}" active>Parent</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Children</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View all children linked to your account.</p>
    </div>

    <x-ui.card>
        <div class="p-6">
            @forelse($children as $child)
                <a href="{{ route('parent.children.show', $child) }}" class="flex items-center gap-4 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors group mb-3 last:mb-0">
                    <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-lg flex-shrink-0">
                        {{ substr($child->full_name ?? $child->admission_no, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ $child->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $child->class->name ?? 'Unassigned' }} &middot; {{ $child->admission_no ?? 'N/A' }}</p>
                    </div>
                    <svg class="h-5 w-5 text-neutral-400 group-hover:text-primary-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No children linked to your account.</p>
                </div>
            @endforelse
        </div>
    </x-ui.card>
</x-layouts.app>