@props(['title' => 'Chart', 'height' => 300])

<div class="bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border p-6 shadow-premium">
    @if($title)
        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">{{ $title }}</h3>
    @endif
    <div class="relative" style="height: {{ $height }}px;">
        {{ $slot }}
    </div>
</div>
