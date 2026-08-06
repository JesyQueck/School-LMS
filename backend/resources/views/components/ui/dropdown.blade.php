@props([
    'trigger' => '',
    'align' => 'right',
])

@php
    $alignClasses = [
        'right' => 'right-0',
        'left' => 'left-0',
    ];
@endphp

<div class="relative" x-data="{ open: false }">
    <div @click="open = !open">
        {{ $trigger }}
    </div>
    <div
        x-show="open"
        x-cloak
        @click.away="open = false"
        @keydown.escape="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute {{ $alignClasses[$align] }} mt-2 z-50 w-56 rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface shadow-premium-lg py-2"
        role="menu"
    >
        {{ $slot }}
    </div>
</div>
