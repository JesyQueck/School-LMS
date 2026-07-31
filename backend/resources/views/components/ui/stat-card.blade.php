@props(['label' => '', 'value' => '', 'trend' => null, 'icon' => ''])

@php
    $trendColors = [
        'up' => 'text-success-600 dark:text-success-400',
        'down' => 'text-danger-600 dark:text-danger-400',
    ];
@endphp

<div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">{{ $value }}</p>
            @if($trend)
                <p class="mt-2 flex items-center gap-1 text-sm {{ $trendColors[$trend['direction']] ?? 'text-neutral-500' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trend['direction'] === 'up' ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                    {{ $trend['value'] }}
                </p>
            @endif
        </div>
        @if($icon)
            <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
            </div>
        @endif
    </div>
</div>
