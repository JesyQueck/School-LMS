@props(['title' => '', 'date' => '', 'time' => '', 'location' => '', 'description' => '', 'href' => '#'])

@php
    $iconPaths = [
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'map-pin' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
    ];

    $dateObj = \Carbon\Carbon::parse($date);
    $day = $dateObj->format('d');
    $month = $dateObj->format('M');
@endphp

<div class="flex gap-4 bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-4 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex flex-col items-center justify-center bg-primary-50 dark:bg-primary-900/20 rounded-lg px-3 py-2 min-w-[60px]">
        <span class="text-xs font-medium text-primary-600 dark:text-primary-400 uppercase tracking-wide">{{ $month }}</span>
        <span class="text-xl font-bold text-primary-700 dark:text-primary-300 leading-none">{{ $day }}</span>
    </div>
    <div class="flex-1 min-w-0">
        <h3 class="text-base font-semibold text-neutral-900 dark:text-white mb-1 truncate">{{ $title }}</h3>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-500 dark:text-neutral-400 mb-2">
            @if($time)
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['clock'] }}"/></svg>
                    {{ $time }}
                </span>
            @endif
            @if($location)
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['map-pin'] }}"/></svg>
                    {{ $location }}
                </span>
            @endif
        </div>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 line-clamp-2">{{ $description }}</p>
    </div>
</div>
