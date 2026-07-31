@props(['pages' => []])

@php
    $iconPaths = [
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10m-2 2h-4m4 0h4',
        'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'clipboard-list' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'book-open' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'image' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        'newspaper' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9a2 2 0 00-2 2v1m-4 9v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2a2 2 0 00-2 2z',
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'graduation-cap' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7',
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'x' => 'M18 6L6 18M6 6l12 12',
    ];

    $navPages = $pages ?: [
        ['href' => '/', 'label' => 'Home', 'icon' => 'home'],
        ['href' => '/about', 'label' => 'About', 'icon' => 'info'],
        ['href' => '/admissions', 'label' => 'Admissions', 'icon' => 'clipboard-list'],
        ['href' => '/academics', 'label' => 'Academics', 'icon' => 'book-open'],
        ['href' => '/gallery', 'label' => 'Gallery', 'icon' => 'image'],
        ['href' => '/news', 'label' => 'News', 'icon' => 'newspaper'],
        ['href' => '/contact', 'label' => 'Contact', 'icon' => 'mail'],
    ];
@endphp

<nav class="bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border sticky top-0 z-50 transition-colors duration-150" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-8">
                <a href="/" class="flex items-center gap-2 shrink-0">
                    <div class="h-8 w-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-sm">GA</div>
                    <span class="text-lg font-bold text-neutral-900 dark:text-white">Greenfield Academy</span>
                </a>

                <div class="hidden lg:flex items-center gap-1">
                    @foreach($navPages as $page)
                        <a href="{{ $page['href'] }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors {{ request()->is(ltrim(parse_url($page['href'], PHP_URL_PATH), '/')) && $page['href'] !== '/' ? 'text-primary-600 dark:text-primary-400' : '' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$page['icon']] ?? '' }}"/></svg>
                            {{ $page['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="hidden lg:flex items-center gap-3">
                <a href="/student/dashboard" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">Student Portal</a>
                <a href="/parent/dashboard" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">Parent Portal</a>
                <a href="/teacher/dashboard" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">Teacher Portal</a>
                <a href="/login" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-primary-600 hover:bg-primary-700 text-white text-sm px-4 py-2 shadow-sm">Login</a>
            </div>

            <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 focus-visible-ring" aria-label="Toggle menu" aria-expanded="false" x-bind:aria-expanded="mobileOpen.toString()">
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['menu'] }}"/></svg>
                <svg x-show="mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['x'] }}"/></svg>
            </button>
        </div>
    </div>

    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-cloak class="lg:hidden border-t border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface">
        <div class="px-4 py-3 space-y-1">
            @foreach($navPages as $page)
                <a href="{{ $page['href'] }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors {{ request()->is(ltrim(parse_url($page['href'], PHP_URL_PATH), '/')) && $page['href'] !== '/' ? 'text-primary-600 dark:text-primary-400' : '' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$page['icon']] ?? '' }}"/></svg>
                    {{ $page['label'] }}
                </a>
            @endforeach
            <div class="border-t border-neutral-200 dark:border-dark-border pt-2 mt-2 space-y-1">
                <a href="/student/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['user'] }}"/></svg>
                    Student Portal
                </a>
                <a href="/parent/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['users'] }}"/></svg>
                    Parent Portal
                </a>
                <a href="/teacher/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['graduation-cap'] }}"/></svg>
                    Teacher Portal
                </a>
                <a href="/login" class="flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-primary-600 hover:bg-primary-700 text-white text-sm px-4 py-2 shadow-sm mt-2">
                    Login
                </a>
            </div>
        </div>
    </div>
</nav>
