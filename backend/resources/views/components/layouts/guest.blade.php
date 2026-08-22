<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Greenfield Academy') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <meta name="description" content="Greenfield Academy - Nurturing young minds through academic excellence, creative thinking, and strong moral values.">
    <meta property="og:title" content="{{ config('school.name', 'Greenfield Academy') }}">
    <meta property="og:description" content="Greenfield Academy - Nurturing young minds through academic excellence, creative thinking, and strong moral values.">
    <meta property="og:image" content="{{ asset(config('school.logo', 'images/logo.svg')) }}">
    <meta property="og:image:type" content="image/svg+xml">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
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
