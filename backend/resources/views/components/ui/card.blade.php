@props([
    'hoverable' => false,
    'padding' => true,
    'elevated' => false,
])

@php
    $baseClasses = 'bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border ';
    $shadowClasses = $elevated ? 'shadow-premium-lg' : 'shadow-premium';
    $hoverClasses = $hoverable ? 'hover:shadow-premium-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer' : '';
@endphp

<div {{ $attributes->merge(['class' => $baseClasses . $shadowClasses . ' ' . $hoverClasses]) }}>
    @if($padding)
        <div class="px-6 sm:px-8 py-6">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</div>
