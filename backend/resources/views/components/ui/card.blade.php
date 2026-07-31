@props([
    'hoverable' => false,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm ' . ($hoverable ? 'hover:shadow-md transition-shadow cursor-pointer' : '')]) }}>
    @if($padding)
        <div class="px-6 py-4">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</div>
