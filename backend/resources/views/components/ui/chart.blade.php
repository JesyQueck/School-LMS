@props(['title' => 'Chart', 'height' => 300])

<div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm">
    @if($title)
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">{{ $title }}</h3>
    @endif
    <div class="relative" style="height: {{ $height }}px;">
        {{ $slot }}
    </div>
</div>
