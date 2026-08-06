@props([
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white shadow-sm hover:shadow-md',
        'secondary' => 'bg-white dark:bg-dark-surface border-2 border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700',
        'accent' => 'bg-accent-500 hover:bg-accent-600 active:bg-accent-700 text-white shadow-sm hover:shadow-md',
        'danger' => 'bg-danger-600 hover:bg-danger-700 active:bg-danger-800 text-white shadow-sm hover:shadow-md',
        'ghost' => 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white',
        'link' => 'text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 underline underline-offset-4',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-2.5 text-base',
        'lg' => 'px-8 py-3 text-lg',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base . ' ' . $variants[$variant] . ' ' . $sizes[$size]]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
