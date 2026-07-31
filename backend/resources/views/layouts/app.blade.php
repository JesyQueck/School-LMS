<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 dark:bg-dark-bg text-neutral-900 dark:text-dark-text transition-colors duration-150">
    <div class="flex h-screen overflow-hidden">
        <aside class="hidden md:flex md:flex-col md:w-64 bg-white dark:bg-dark-surface border-r border-neutral-200 dark:border-dark-border transition-colors duration-150">
            <div class="flex items-center gap-2 h-16 px-6 border-b border-neutral-200 dark:border-dark-border">
                <div class="h-8 w-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold">S</div>
                <span class="text-lg font-bold text-neutral-900 dark:text-white">School LMS</span>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" aria-label="Sidebar">
                <x-layout.sidebar-item href="/admin/dashboard" icon="layout-dashboard" label="Dashboard" />
                <x-layout.sidebar-item href="/admin/classes" icon="school" label="Classes" />
                <x-layout.sidebar-item href="/admin/teachers" icon="graduation-cap" label="Teachers" />
                <x-layout.sidebar-item href="/admin/students" icon="users" label="Students" />
                <x-layout.sidebar-item href="/admin/results" icon="clipboard-list" label="Results" />
                <x-layout.sidebar-item href="/admin/finance" icon="wallet" label="Finance" />
                <x-layout.sidebar-item href="/admin/report-cards" icon="file-text" label="Report Cards" />
                <x-layout.sidebar-item href="/admin/assignments" icon="book-open" label="Assignments" />
                <x-layout.sidebar-item href="/admin/academic" icon="calendar" label="Academic" />
            </nav>
            <div class="p-4 border-t border-neutral-200 dark:border-dark-border">
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-lg transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>
        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="h-16 bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border flex items-center justify-between px-4 sm:px-6 lg:px-8 transition-colors duration-150">
                <div class="flex items-center gap-4">
                    <button class="md:hidden p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800" aria-label="Open menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="$store.theme.toggle()" class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400" aria-label="Toggle theme">
                        <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-sm font-medium text-primary-700 dark:text-primary-300">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
            <footer class="bg-white dark:bg-dark-surface border-t border-neutral-200 dark:border-dark-border px-4 sm:px-6 lg:px-8 py-3">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; {{ date('Y') }} School LMS. All rights reserved.</p>
            </footer>
        </div>
    </div>
</body>
</html>
