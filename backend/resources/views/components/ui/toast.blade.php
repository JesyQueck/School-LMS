@props(['variant' => 'info'])

@php
    $variants = [
        'success' => ['bg' => 'bg-white dark:bg-dark-surface', 'border' => 'border-success-200 dark:border-success-800', 'text' => 'text-success-800 dark:text-success-200', 'icon' => 'text-success-500'],
        'error' => ['bg' => 'bg-white dark:bg-dark-surface', 'border' => 'border-danger-200 dark:border-danger-800', 'text' => 'text-danger-800 dark:text-danger-200', 'icon' => 'text-danger-500'],
        'info' => ['bg' => 'bg-white dark:bg-dark-surface', 'border' => 'border-info-200 dark:border-info-800', 'text' => 'text-info-800 dark:text-info-200', 'icon' => 'text-info-500'],
    ];
    $v = $variants[$variant];
@endphp

<div {{ $attributes->merge(['class' => "{$v['bg']} {$v['border']} {$v['text']} px-4 py-3 rounded-lg shadow-lg border flex items-center gap-3 min-w-[300px] max-w-sm"]) }}>
    <svg class="h-5 w-5 flex-shrink-0 {{ $v['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="flex-1">{{ $slot }}</div>
</div>
