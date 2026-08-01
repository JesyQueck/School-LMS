@props(['title' => '', 'description' => '', 'icon' => ''])

@php
    $iconPaths = [
        'book-open' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'flask-conical' => 'M19.428 13.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 13.48a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
        'music' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
        'trophy' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        'globe' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'cpu' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
        'palette' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
        'dumbbell' => 'M6 5v14M18 5v14M2 11h20M6 11h12M6 13h12M2 13h20',
    ];

    $facilityImages = [
        'book-open' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'flask-conical' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'cpu' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'music' => 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'trophy' => 'https://images.unsplash.com/photo-1526232761682-d26e03ac148e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'palette' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'globe' => 'https://images.unsplash.com/photo-1488190211105-8b0ee654eaf2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'dumbbell' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac2934?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    ];
    $imgSrc = $facilityImages[$icon] ?? 'https://images.unsplash.com/photo-1580582932705-53aed7dede1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
@endphp

<div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium overflow-hidden card-lift hover:shadow-premium-lg">
    <div class="aspect-16/10 overflow-hidden relative">
        <img src="{{ $imgSrc }}" alt="{{ $title }}" class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy">
        <div class="absolute inset-0 bg-linear-to-t from-black/40 to-transparent"></div>
        <div class="absolute bottom-4 left-4 h-11 w-11 rounded-xl bg-white/90 dark:bg-dark-surface/90 backdrop-blur-sm flex items-center justify-center shadow-lg">
            <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$icon] ?? $icon }}"/></svg>
        </div>
    </div>
    <div class="p-6">
        <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-2">{{ $title }}</h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">{{ $description }}</p>
    </div>
</div>
