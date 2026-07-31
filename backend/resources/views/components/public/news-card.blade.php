@props(['title' => '', 'excerpt' => '', 'date' => '', 'author' => '', 'image' => null, 'href' => '#'])

@php
    $iconPaths = [
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    ];
@endphp

<a href="{{ $href }}" class="group block bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden hover:shadow-md transition-shadow">
    <div class="aspect-video bg-gradient-to-br from-primary-100 to-accent-100 dark:from-primary-900/30 dark:to-accent-900/30 flex items-center justify-center">
        <svg class="h-10 w-10 text-primary-300 dark:text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9a2 2 0 00-2 2v1m-4 9v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/></svg>
    </div>
    <div class="p-5">
        <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400 mb-3">
            <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['calendar'] }}"/></svg>
                {{ $date }}
            </span>
            <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['user'] }}"/></svg>
                {{ $author }}
            </span>
        </div>
        <h3 class="text-base font-semibold text-neutral-900 dark:text-white mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-2">{{ $title }}</h3>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed line-clamp-3">{{ $excerpt }}</p>
    </div>
</a>
