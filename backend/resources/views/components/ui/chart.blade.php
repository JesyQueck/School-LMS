@props(['title' => 'Chart', 'description' => null, 'height' => 300])

<div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-5 shadow-sm">
    @if($title)
        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">{{ $title }}</h3>
    @endif
    @if($description)
        <p class="mt-1 mb-4 text-sm text-neutral-500 dark:text-neutral-400">{{ $description }}</p>
    @endif
    <div class="relative" style="height: {{ $height }}px;">
        {{ $slot }}
    </div>
</div>
