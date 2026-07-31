<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 dark:bg-dark-bg text-neutral-900 dark:text-dark-text transition-colors duration-150">
    <header class="h-16 bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border flex items-center justify-between px-4 sm:px-6 lg:px-8 transition-colors duration-150">
        <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold">S</div>
            <span class="text-lg font-bold text-neutral-900 dark:text-white">School LMS</span>
        </div>
        <nav class="flex items-center gap-6">
            <a href="/about" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">About</a>
            <a href="/admissions" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">Admissions</a>
            <a href="/contact" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">Contact</a>
            <a href="/login" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">Login</a>
            <button @click="$store.theme.toggle()" class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400" aria-label="Toggle theme">
                <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
        </nav>
    </header>
    <main class="min-h-[calc(100vh-64px)]">
        {{ $slot }}
    </main>
    <footer class="bg-white dark:bg-dark-surface border-t border-neutral-200 dark:border-dark-border px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded bg-primary-600 flex items-center justify-center text-white text-xs font-bold">S</div>
                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">School LMS</span>
            </div>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; {{ date('Y') }} School LMS. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
