@props(['title' => '', 'excerpt' => '', 'date' => '', 'author' => '', 'image' => null, 'href' => '#', 'category' => null])

@php
    $iconPaths = [
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'arrow-right' => 'M14 5l7 7m0 0l-7 7m7-7H3',
    ];

    $imageUrls = [
        'default' => 'https://images.unsplash.com/photo-1523240795612-977054012b66?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'school' => 'https://images.unsplash.com/photo-1580582932705-53aed7dede1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'science' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'sports' => 'https://images.unsplash.com/photo-1526232761682-d26e03ac148e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'meeting' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    ];
    $imgSrc = $imageUrls[$image] ?? $image ?? $imageUrls['default'];
@endphp

<a href="{{ $href }}" class="group block bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium overflow-hidden card-lift hover:shadow-premium-lg">
    <div class="aspect-16/10 overflow-hidden relative">
        <img src="{{ $imgSrc }}" alt="{{ $title }}" class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy">
        @if($category)
            <span class="absolute top-4 left-4 inline-flex items-center rounded-full bg-white/90 dark:bg-dark-surface/90 backdrop-blur-sm px-3 py-1 text-xs font-semibold text-primary-700 dark:text-primary-300 shadow-sm">
                {{ $category }}
            </span>
        @endif
    </div>
    <div class="p-6">
        <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400 mb-3">
            <span class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['calendar'] }}"/></svg>
                {{ $date }}
            </span>
            <span class="text-neutral-300 dark:text-neutral-700">&middot;</span>
            <span class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['user'] }}"/></svg>
                {{ $author }}
            </span>
        </div>
        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2.5 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors leading-snug line-clamp-2">{{ $title }}</h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed line-clamp-3 mb-4">{{ $excerpt }}</p>
        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 dark:text-primary-400 group-hover:gap-2.5 transition-all">
            Read more
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['arrow-right'] }}"/></svg>
        </span>
    </div>
</a>
