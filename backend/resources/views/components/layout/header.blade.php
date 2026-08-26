@props(['title' => 'Dashboard', 'actions' => null])

<header class="h-16 bg-primary-800 text-white border-b border-white/20 shadow-lg flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0">
    <div class="flex items-center gap-4">
        <label for="sidebar-menu-checkbox"
               class="lg:hidden cursor-pointer rounded-xl p-1.5 text-white hover:bg-white/10 focus-visible-ring transition-colors duration-200"
               aria-label="Open menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="4" y1="6" x2="20" y2="6"/>
                <line x1="4" y1="12" x2="20" y2="12"/>
                <line x1="4" y1="18" x2="20" y2="18"/>
            </svg>
        </label>

        <h1 class="text-lg font-bold tracking-tight truncate">
            {{ $title ?? 'Dashboard' }}
        </h1>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        {{ $actions ?? '' }}

        <button x-data @click="$store.theme.toggle()" type="button"
                class="group inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-white hover:bg-white/10 focus-visible-ring transition-colors duration-200"
                aria-label="Toggle theme" id="theme-toggle">
            <svg x-show="!$store.theme.dark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="$store.theme.dark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="text-sm font-medium text-white/90 group-hover:text-white transition-colors lg:inline hidden">Toggle Theme</span>
        </button>
    </div>
</header>
