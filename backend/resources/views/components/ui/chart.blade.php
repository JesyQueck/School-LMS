@props(['title' => 'Chart', 'description' => null, 'height' => 300])

<div {{ $attributes->merge(['class' => 'min-w-0 rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-dark-border dark:bg-dark-surface']) }}>
    @if($title)
        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">{{ $title }}</h3>
    @endif
    @if($description)
        <p class="mt-1 mb-4 text-sm text-neutral-500 dark:text-neutral-400">{{ $description }}</p>
    @endif
    <div class="relative min-w-0 w-full" style="height: {{ $height }}px;">
        {{ $slot }}
    </div>
</div>
