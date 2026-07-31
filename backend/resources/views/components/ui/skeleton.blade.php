@props(['variant' => 'text'])

<div class="animate-pulse space-y-3">
    @if($variant === 'text')
        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4"></div>
        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-1/2"></div>
        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-5/6"></div>
    @elseif($variant === 'title')
        <div class="h-6 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4 mb-4"></div>
        <div class="space-y-3">
            <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
            <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-5/6"></div>
        </div>
    @elseif($variant === 'card')
        <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="h-10 w-10 bg-neutral-200 dark:bg-neutral-700 rounded-full"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-1/2"></div>
                    <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-1/3"></div>
                </div>
            </div>
            <div class="space-y-2">
                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-4/5"></div>
            </div>
        </div>
    @elseif($variant === 'avatar')
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 bg-neutral-200 dark:bg-neutral-700 rounded-full"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-1/2"></div>
                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-1/3"></div>
            </div>
        </div>
    @endif
</div>
