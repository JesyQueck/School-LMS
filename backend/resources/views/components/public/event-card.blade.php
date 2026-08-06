@props(['title' => '', 'date' => '', 'time' => '', 'location' => '', 'description' => '', 'href' => '#'])

@php
    $iconPaths = [
        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'map-pin' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        'arrow-right' => 'M14 5l7 7m0 0l-7 7m7-7H3',
    ];

    $dateObj = \Carbon\Carbon::parse($date);
    $day = $dateObj->format('d');
    $month = $dateObj->format('M');
    $weekday = $dateObj->format('l');
@endphp

<a href="{{ $href }}" class="group flex gap-5 bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border p-5 shadow-premium card-lift hover:shadow-premium-lg">
    <div class="flex flex-col items-center justify-center bg-linear-to-br from-primary-500 to-primary-700 rounded-2xl px-4 py-3 min-w-[72px] text-white shadow-lg shrink-0">
        <span class="text-[10px] font-bold uppercase tracking-wider opacity-90">{{ $month }}</span>
        <span class="text-2xl font-bold leading-none mt-0.5">{{ $day }}</span>
        <span class="text-[9px] font-semibold opacity-75 mt-1">{{ $weekday }}</span>
    </div>
    <div class="flex-1 min-w-0">
        <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $title }}</h3>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-neutral-500 dark:text-neutral-400 mb-2.5">
            @if($time)
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['clock'] }}"/></svg>
                    {{ $time }}
                </span>
            @endif
            @if($location)
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['map-pin'] }}"/></svg>
                    {{ $location }}
                </span>
            @endif
        </div>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed line-clamp-2">{{ $description }}</p>
    </div>
</a>
