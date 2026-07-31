@props([
    'title' => '',
    'subtitle' => '',
    'align' => 'center',
    'badge' => null,
])

@php
    $alignments = [
        'center' => 'text-center',
        'left' => 'text-left',
    ];
@endphp

<div class="{{ $alignments[$align] ?? 'text-center' }} mb-12">
    @if($badge)
        <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 mb-4">
            {{ $badge }}
        </span>
    @endif
    <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight mb-4">
        {{ $title }}
    </h2>
    @if($subtitle)
        <p class="text-lg text-neutral-600 dark:text-neutral-400 max-w-2xl mx-auto leading-relaxed">
            {{ $subtitle }}
        </p>
    @endif
</div>
