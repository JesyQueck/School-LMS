@props(['variant' => 'info'])

@php
    $variants = [
        'success' => ['bg' => 'bg-success-50 dark:bg-success-950/40', 'border' => 'border-l-4 border-success-200 dark:border-success-800', 'text' => 'text-success-800 dark:text-success-300', 'icon' => 'text-success-600 dark:text-success-400'],
        'warning' => ['bg' => 'bg-warning-50 dark:bg-warning-950/40', 'border' => 'border-l-4 border-warning-200 dark:border-warning-800', 'text' => 'text-warning-800 dark:text-warning-300', 'icon' => 'text-warning-600 dark:text-warning-400'],
        'danger' => ['bg' => 'bg-danger-50 dark:bg-danger-950/40', 'border' => 'border-l-4 border-danger-200 dark:border-danger-800', 'text' => 'text-danger-800 dark:text-danger-300', 'icon' => 'text-danger-600 dark:text-danger-400'],
        'info' => ['bg' => 'bg-info-50 dark:bg-info-950/40', 'border' => 'border-l-4 border-info-200 dark:border-info-800', 'text' => 'text-info-800 dark:text-info-300', 'icon' => 'text-info-600 dark:text-info-400'],
    ];
    $v = $variants[$variant];
@endphp

<div {{ $attributes->merge(['class' => "{$v['bg']} {$v['border']} {$v['text']} px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm"]) }} role="alert">
    <svg class="h-5 w-5 flex-shrink-0 {{ $v['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="flex-1 text-sm font-medium">{{ $slot }}</div>
</div>
