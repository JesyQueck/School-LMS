@props([
    'title',
    'subtitle' => null,
    'backHref' => null,
    'backLabel' => 'Back',
])

<div class="mb-6">
    @isset($breadcrumbs)
        {{ $breadcrumbs }}
    @endisset

    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            @if($backHref)
                <a href="{{ $backHref }}" class="mb-2 inline-flex items-center gap-2 rounded text-sm font-medium text-primary-700 hover:text-primary-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-primary-300 dark:hover:text-primary-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ $backLabel }}
                </a>
            @endif
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $title }}</h1>
            @if($subtitle)
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
