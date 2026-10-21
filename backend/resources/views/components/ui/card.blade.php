@props([
    'hoverable' => false,
    'padding' => true,
    'elevated' => false,
])

@php
    $baseClasses = 'bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border ';
    $shadowClasses = $elevated ? 'shadow-md' : 'shadow-sm';
    $hoverClasses = $hoverable ? 'hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer' : '';
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
