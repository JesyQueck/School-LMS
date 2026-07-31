@props(['size' => 'md', 'status' => null])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-lg',
    ];
    $statusColors = [
        'online' => 'bg-success-500',
        'away' => 'bg-warning-500',
        'busy' => 'bg-danger-500',
        'offline' => 'bg-neutral-400',
    ];
@endphp

<div class="relative inline-flex shrink-0">
    <div {{ $attributes->merge(['class' => $sizes[$size] . ' rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium overflow-hidden']) }}>
        {{ $slot }}
    </div>
    @if($status)
        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full {{ $statusColors[$status] ?? $statusColors['offline'] }} border-2 border-white dark:border-dark-surface"></span>
    @endif
</div>
