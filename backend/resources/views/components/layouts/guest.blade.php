<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 dark:bg-dark-bg text-neutral-900 dark:text-dark-text transition-colors duration-150">
    <x-layout.skip-link />

    <x-layout.header title="{{ config('app.name', 'School LMS') }}">
        <nav class="flex items-center gap-6">
            <a href="/about" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">About</a>
            <a href="/admissions" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">Admissions</a>
            <a href="/contact" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">Contact</a>
            <a href="/login" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">Login</a>
        </nav>
    </x-layout.header>

    <main id="main-content" class="min-h-[calc(100vh-64px)]">
        {{ $slot }}
    </main>

    <x-layout.footer>
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded bg-primary-600 flex items-center justify-center text-white text-xs font-bold">S</div>
                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">School LMS</span>
            </div>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; {{ date('Y') }} School LMS. All rights reserved.</p>
        </div>
    </x-layout.footer>
</body>
</html>
