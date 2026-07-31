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
        class="absolute {{ $alignClasses[$align] }} mt-2 z-50 w-56 rounded-xl border border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface shadow-lg py-1"
        role="menu"
    >
        {{ $slot }}
    </div>
</div>
