@props(['variant' => 'spinner', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-5 w-5',
        'md' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
    ];
@endphp

@if($variant === 'spinner')
    <div class="flex items-center justify-center">
        <div class="{{ $sizes[$size] }} border-3 border-neutral-200 dark:border-neutral-700 border-t-primary-600 rounded-full animate-spin"></div>
    </div>
@elseif($variant === 'dots')
    <div class="flex items-center justify-center gap-1.5">
        <div class="h-2.5 w-2.5 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
        <div class="h-2.5 w-2.5 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
        <div class="h-2.5 w-2.5 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
    </div>
@endif
