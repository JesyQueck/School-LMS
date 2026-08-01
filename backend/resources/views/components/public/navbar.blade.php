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

<nav id="public-navbar" class="sticky top-0 z-50 bg-white dark:bg-dark-surface border-b border-neutral-200/60 dark:border-dark-border/60 transition-shadow duration-300">
    <input id="public-menu-checkbox" type="checkbox" class="peer sr-only">
    <label for="public-menu-checkbox" class="absolute right-6 top-4 z-20 lg:hidden p-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 focus-visible-ring transition-colors cursor-pointer block peer-checked:hidden" aria-label="Toggle menu" aria-controls="public-mobile-menu" role="button">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['menu'] }}"/></svg>
    </label>
    <label for="public-menu-checkbox" class="absolute right-6 top-4 z-20 lg:hidden p-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 focus-visible-ring transition-colors cursor-pointer hidden peer-checked:block" aria-label="Close menu" aria-controls="public-mobile-menu" role="button">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['x'] }}"/></svg>
    </label>
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
        <div class="flex items-center justify-between h-18">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 shrink-0 group">
                <div class="h-9 w-9 rounded-xl bg-linear-to-br from-primary-600 to-primary-700 flex items-center justify-center text-white font-bold text-sm shadow-premium transition-transform duration-300 group-hover:scale-105">GA</div>
                <div class="flex flex-col leading-none">
                    <span class="text-[15px] font-bold text-neutral-900 dark:text-white tracking-tight">Greenfield Academy</span>
                    <span class="text-[10px] font-medium text-neutral-400 dark:text-neutral-500 tracking-wide uppercase mt-0.5">Est. 1995</span>
                </div>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden lg:flex items-center gap-1">
                @foreach($navPages as $page)
                    <a href="{{ $page['href'] }}"
                       class="relative px-4 py-2 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100/70 dark:hover:bg-neutral-800/50 transition-colors {{ request()->is(ltrim(parse_url($page['href'], PHP_URL_PATH), '/')) && $page['href'] !== '/' ? 'text-primary-600 dark:text-primary-400' : '' }}">
                        {{ $page['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Desktop actions --}}
            <div class="hidden lg:flex items-center gap-2">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border text-neutral-800 dark:text-neutral-200 text-sm px-5 py-2.5 shadow-premium hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700">
                        Register
                    </a>
                @endif
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-primary-600 hover:bg-primary-700 text-white text-sm px-5 py-2.5 shadow-premium hover:shadow-premium-lg btn-shine">
                    Portal Login
                    <svg class="ml-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['arrow-right'] }}"/></svg>
                </a>
            </div>

            {{-- Mobile toggle --}}
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="public-mobile-menu" class="hidden peer-checked:block lg:hidden bg-white dark:bg-dark-surface border-t border-neutral-200/60 dark:border-dark-border/60">
        <div class="px-6 py-4 space-y-1">
            @foreach($navPages as $page)
                <a href="{{ $page['href'] }}" onclick="document.getElementById('public-menu-checkbox').checked = false;" class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors {{ request()->is(ltrim(parse_url($page['href'], PHP_URL_PATH), '/')) && $page['href'] !== '/' ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    {{ $page['label'] }}
                    <svg class="h-4 w-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['arrow-right'] }}"/></svg>
                </a>
            @endforeach
            <div class="border-t border-neutral-200 dark:border-dark-border pt-3 mt-3 space-y-2">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" onclick="document.getElementById('public-menu-checkbox').checked = false;" class="flex items-center justify-center font-semibold rounded-xl transition-colors duration-150 bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border text-neutral-800 dark:text-neutral-200 text-sm px-4 py-3 shadow-premium hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700">
                        Register
                    </a>
                @endif
                <a href="{{ route('login') }}" onclick="document.getElementById('public-menu-checkbox').checked = false;" class="flex items-center justify-center font-semibold rounded-xl transition-colors duration-150 bg-primary-600 hover:bg-primary-700 text-white text-sm px-4 py-3 shadow-premium">
                    Portal Login
                    <svg class="ml-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['arrow-right'] }}"/></svg>
                </a>
            </div>
        </div>
    </div>
</nav>
