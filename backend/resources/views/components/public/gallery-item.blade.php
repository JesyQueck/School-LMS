                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        lll@props(['title' => '', 'category' => '', 'image' => null, 'href' => '#'])

@php
    $galleryImages = [
        'Classroom Learning' => asset('images/classroom.svg'),
        'Sports Day' => asset('images/student-life.svg'),
        'Science Lab' => asset('images/science_lab_img.png'),
        'Art Class' => asset('images/facilities_img1.png'),
        'Library' => asset('images/facilities_img1.png'),
        'Music Room' => asset('images/facilities_img1.png'),
        'Assembly' => asset('images/student-life.svg'),
        'Field Trip' => asset('images/facilities_img1.png'),
        'Library Reading' => asset('images/facilities_img1.png'),
        'Football Match' => asset('images/student-life.svg'),
        'Athletics Track' => asset('images/student-life.svg'),
        'Art Exhibition' => asset('images/facilities_img1.png'),
        'Music Performance' => asset('images/facilities_img1.png'),
        'Drama Club' => asset('images/facilities_img1.png'),
        'Graduation Day' => asset('images/student-life.svg'),
        'Prize Giving' => asset('images/facilities_img1.png'),
        'Cultural Day' => asset('images/student-life.svg'),
        'Science Fair' => asset('images/science_lab_img.png'),
        'Swimming' => asset('images/student-life.svg'),
        'Talent Show' => asset('images/facilities_img1.png'),
    ];
    $imgSrc = $image ?? $galleryImages[$title] ?? asset('images/facilities_img1.png');
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
