<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Greenfield Academy') }}</title>
    <meta name="description" content="Greenfield Academy - Nurturing young minds through academic excellence, creative thinking, and strong moral values.">
    <link rel="canonical" href="{{ url()->current() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 dark:bg-dark-bg text-neutral-900 dark:text-dark-text transition-colors duration-150">
    <x-layout.skip-link />

    <x-public.navbar />

    <main id="main-content" class="min-h-[calc(100vh-64px)]">
        {{ $slot }}
    </main>

    <x-public.footer />
</body>
</html>
