@props(['title' => '', 'category' => '', 'image' => null, 'href' => '#'])

@php
    $galleryImages = [
        'Classroom Learning' => 'https://images.unsplash.com/photo-1580582932705-53aed7dede1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'Sports Day' => 'https://images.unsplash.com/photo-1526232761682-d26e03ac148e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'Science Lab' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'Art Class' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'Library' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'Music Room' => 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'Assembly' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'Field Trip' => 'https://images.unsplash.com/photo-1503676267431-0d262eb9f1b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    ];
    $imgSrc = $image ?? $galleryImages[$title] ?? 'https://images.unsplash.com/photo-1580582932705-53aed7dede1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
@endphp

<div class="group relative aspect-square rounded-2xl overflow-hidden cursor-pointer image-zoom shadow-premium hover:shadow-premium-lg transition-shadow duration-300">
    <img src="{{ $imgSrc }}" alt="{{ $title }}" class="w-full h-full object-cover zoom-target" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
    <div class="absolute inset-0 bg-linear-to-t from-black/70 via-black/10 to-transparent opacity-60 group-hover:opacity-90 transition-opacity duration-300"></div>
    <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-1 group-hover:translate-y-0 transition-transform duration-300">
        <p class="text-sm font-bold text-white">{{ $title }}</p>
        @if($category)
            <p class="text-xs text-white/70 mt-0.5">{{ $category }}</p>
        @endif
    </div>
</div>
