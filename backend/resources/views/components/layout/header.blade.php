<header class="h-16 bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border flex items-center justify-between px-4 sm:px-6 lg:px-8 transition-colors duration-150 shrink-0">
    <div class="flex items-center gap-4">
        <button
            @click="$store.sidebar.toggle()"
            class="lg:hidden p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 focus-visible-ring"
            aria-label="Open menu"
            aria-expanded="false"
            x-bind:aria-expanded="$store.sidebar.open"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div>
            <h1 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $title }}</h1>
        </div>
    </div>
    <div class="flex items-center gap-2 sm:gap-3">
        {{ $actions ?? '' }}
        <button
            @click="$store.theme.toggle()"
            class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 focus-visible-ring"
            aria-label="Toggle theme"
        >
            <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
        <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-sm font-medium text-primary-700 dark:text-primary-300">
            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
        </div>
    </div>
</header>
