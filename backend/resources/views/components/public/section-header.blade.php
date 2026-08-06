@props([
    'title' => '',
    'subtitle' => '',
    'align' => 'center',
    'badge' => null,
])

@php
    $alignments = [
        'center' => 'text-center mx-auto',
        'left' => 'text-left',
    ];
@endphp

<div class="{{ $alignments[$align] ?? 'text-center mx-auto' }} mb-12 sm:mb-16 lg:mb-20 max-w-2xl animate-on-scroll">
    @if($badge)
        <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/40 px-3.5 py-1.5 text-xs font-bold text-primary-700 dark:text-primary-300 mb-5">
            <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
            {{ $badge }}
        </span>
    @endif
    <h2 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-neutral-900 dark:text-white tracking-tight leading-[1.15] mb-4">
        {{ $title }}
    </h2>
    @if($subtitle)
        <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">
            {{ $subtitle }}
        </p>
    @endif
</div>
