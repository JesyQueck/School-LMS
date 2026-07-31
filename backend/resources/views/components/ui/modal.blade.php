@props(['show' => false, 'maxWidth' => 'lg'])

@php
    $maxWidths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
@endphp

<div 
    x-show="show" 
    x-cloak
    @keydown.escape.window="show = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
>
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="show = false"></div>
    <div class="relative bg-white dark:bg-dark-surface rounded-xl shadow-xl border border-neutral-200 dark:border-dark-border w-full {{ $maxWidths[$maxWidth] }} max-h-[90vh] overflow-y-auto">
        {{ $slot }}
    </div>
</div>
