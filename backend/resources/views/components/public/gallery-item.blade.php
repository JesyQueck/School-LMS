@props(['title' => '', 'category' => '', 'image' => null, 'href' => '#'])

@php
    $galleryImages = [
        'Classroom Learning' => asset('images/learning_img.webp'),
        'Sports Day' => asset('images/Sports_day_img.webp'),
        'Science Lab' => asset('images/Science_room_img.webp'),
        'Art Class' => asset('images/Art_class_img.webp'),
        'Library' => asset('images/library_img.webp'),
        'Music Room' => asset('images/Music_room_img.webp'),
        'Assembly' => asset('images/Assembly_img.webp'),
        'Field Trip' => asset('images/Field_trip_img.webp'),
        'Library Reading' => asset('images/reading_img.webp'),
        'Football Match' => asset('images/student_life_img.webp'),
        'Athletics Track' => asset('images/student_life_img.webp'),
        'Art Exhibition' => asset('images/Art_class_img.webp'),
        'Music Performance' => asset('images/Music_room_img.webp'),
        'Drama Club' => asset('images/facilities_img1.webp'),
        'Graduation Day' => asset('images/student_life_img.webp'),
        'Prize Giving' => asset('images/student_life_img.webp'),
        'Cultural Day' => asset('images/student_life_img.webp'),
        'Science Fair' => asset('images/Science_achievement_img.webp'),
        'Swimming' => asset('images/student_life_img.webp'),
        'Talent Show' => asset('images/facilities_img1.webp'),
    ];
    $imgSrc = $image ?? $galleryImages[$title] ?? asset('images/facilities_img1.webp');
@endphp

<div class="group relative aspect-square rounded-2xl overflow-hidden cursor-pointer image-zoom shadow-premium hover:shadow-premium-lg transition-all duration-300">
    <img src="{{ $imgSrc }}" alt="{{ $title }}" class="w-full h-full object-cover zoom-target" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
    <div class="absolute inset-0 bg-linear-to-t from-black/70 via-black/10 to-transparent opacity-60 group-hover:opacity-90 transition-opacity duration-300"></div>
    <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-1 group-hover:translate-y-0 transition-transform duration-300">
        <p class="text-sm font-bold text-white">{{ $title }}</p>
        @if($category)
            <p class="text-xs text-white/70 mt-0.5">{{ $category }}</p>
        @endif
    </div>
</div>
