@props(['variant' => 'neutral'])

@php
    $variants = [
        'primary' => 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300',
        'success' => 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300',
        'warning' => 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300',
        'danger' => 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300',
        'neutral' => 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . $variants[$variant]]) }}>
    {{ $slot }}
</span>
