@props(['pages' => []])

@php
    $iconPaths = [
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'x' => 'M18 6L6 18M6 6l12 12',
        'arrow-right' => 'M14 5l7 7m0 0l-7 7m7-7H3',
        'chevron-down' => 'M19 9l-7 7-7-7',
    ];

    $navPages = $pages ?: [
        ['href' => '/', 'label' => 'Home'],
        ['href' => '/about', 'label' => 'About'],
        ['href' => '/admissions', 'label' => 'Admissions'],
        ['href' => '/academics', 'label' => 'Academics'],
        ['href' => '/gallery', 'label' => 'Gallery'],
        ['href' => '/news', 'label' => 'News'],
        ['href' => '/contact', 'label' => 'Contact'],
    ];
@endphp

<nav id="public-navbar" x-data @keydown.escape.window="$store.publicMenu.close()" class="sticky top-0 z-50 bg-white/80 dark:bg-dark-surface/80 backdrop-blur-md border-b border-neutral-200/60 dark:border-dark-border/60 transition-all duration-200">
    {{-- Mobile menu state is driven by the Alpine $store.publicMenu store,
         so the hamburger/close labels can live anywhere in normal flow and
         don't depend on CSS peer/sibling positioning. --}}
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
        <div class="flex items-center justify-between h-20">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 shrink-0 group">
                <div class="h-10 w-10 rounded-xl bg-linear-to-br from-primary-600 to-primary-700 flex items-center justify-center text-white font-bold text-sm shadow-premium transition-all duration-300 group-hover:scale-105 group-hover:shadow-premium-lg">GA</div>
                <div class="flex flex-col leading-none">
                    <span class="text-[15px] font-bold text-neutral-900 dark:text-white tracking-tight">Greenfield Academy</span>
                    <span class="text-[10px] font-semibold text-neutral-400 dark:text-neutral-500 tracking-wide uppercase mt-0.5">Est. 1995</span>
                </div>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden lg:flex items-center gap-1">
                @foreach($navPages as $page)
                    <a href="{{ $page['href'] }}"
                       class="relative px-4 py-2 rounded-xl text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100/70 dark:hover:bg-neutral-800/50 transition-all duration-200 {{ request()->is(ltrim(parse_url($page['href'], PHP_URL_PATH), '/')) && $page['href'] !== '/' ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                        {{ $page['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Desktop actions --}}
            <div class="hidden lg:flex items-center gap-3">
                <button
                    @click="$store.theme.toggle()"
                    type="button"
                    class="p-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-all duration-200 focus-visible-ring"
                    aria-label="Toggle theme"
                >
                    <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm px-5 py-2.5 shadow-premium hover:shadow-premium-lg btn-shine">
                    Portal Login
                    <svg class="ml-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['arrow-right'] }}"/></svg>
                </a>
            </div>

            {{-- Mobile controls (visible on < lg): theme toggle + hamburger, side by side --}}
            <div class="lg:hidden flex items-center gap-1">
                <button
                    @click="$store.theme.toggle()"
                    type="button"
                    class="p-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white transition-all duration-200 focus-visible-ring"
                    aria-label="Toggle theme"
                >
                    <svg x-show="!$store.theme.dark" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="$store.theme.dark" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
                <button
                    @click="$store.publicMenu.toggle()"
                    type="button"
                    class="p-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 focus-visible-ring transition-all duration-200 cursor-pointer"
                    :aria-label="$store.publicMenu.open ? 'Close menu' : 'Open menu'"
                    aria-controls="public-mobile-menu"
                    aria-expanded="$store.publicMenu.open"
                >
                    <svg x-show="!$store.publicMenu.open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['menu'] }}"/></svg>
                    <svg x-show="$store.publicMenu.open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['x'] }}"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="public-mobile-menu" x-show="$store.publicMenu.open" x-cloak x-transition class="lg:hidden bg-white/95 dark:bg-dark-surface/95 backdrop-blur-md border-t border-neutral-200/60 dark:border-dark-border/60">
        <div class="px-6 py-4 space-y-1">
            @foreach($navPages as $page)
                <a href="{{ $page['href'] }}" @click="$store.publicMenu.close()" class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all duration-200 {{ request()->is(ltrim(parse_url($page['href'], PHP_URL_PATH), '/')) && $page['href'] !== '/' ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    {{ $page['label'] }}
                    <svg class="h-4 w-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['arrow-right'] }}"/></svg>
                </a>
            @endforeach
            <div class="border-t border-neutral-200 dark:border-dark-border pt-3 mt-3 space-y-2">
                <button
                    @click="$store.theme.toggle()"
                    type="button"
                    class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl text-sm font-semibold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all duration-200"
                    aria-label="Toggle theme"
                >
                    <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-text="$store.theme.dark ? 'Switch to Light' : 'Switch to Dark'">Toggle theme</span>
                </button>
                <a href="{{ route('login') }}" class="flex items-center justify-center w-full px-5 py-3 rounded-xl text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white transition-all duration-200 shadow-premium">
                    Portal Login
                </a>
            </div>
        </div>
    </div>
</nav>
