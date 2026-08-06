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
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="show = false"></div>
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white dark:bg-dark-surface rounded-2xl shadow-premium-xl border-2 border-neutral-200 dark:border-dark-border w-full {{ $maxWidths[$maxWidth] }} max-h-[90vh] overflow-y-auto">
        {{ $slot }}
    </div>
</div>
