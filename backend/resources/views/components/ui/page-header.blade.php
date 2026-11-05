@props([
    'title' => '',
    'subtitle' => null,
    'backHref' => null,
    'backLabel' => 'Back',
])

<div class="mb-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            @if($backHref)
                <a href="{{ $backHref }}" class="inline-flex items-center gap-2 text-sm font-medium text-neutral-500 hover:text-primary-600 dark:text-neutral-400 dark:hover:text-primary-400 mb-3 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ $backLabel }}
                </a>
            @endif

            <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                {{ $title }}
            </h1>

            @if($subtitle)
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ $subtitle }}
                </p>
            @endif

            @if($slot->isNotEmpty())
                <div class="mt-3">
                    {{ $slot }}
                </div>
            @endif
        </div>

        @if(isset($actions))
            <div class="flex flex-shrink-0 items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
