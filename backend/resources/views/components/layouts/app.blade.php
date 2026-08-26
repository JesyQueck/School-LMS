<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Greenfield Academy') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background dark:bg-dark-bg text-neutral-900 dark:text-dark-text transition-colors duration-150">
    <x-layout.skip-link />

    <div class="flex h-screen overflow-hidden bg-background dark:bg-dark-bg">
        <input id="sidebar-menu-checkbox" type="checkbox" class="peer sr-only">

        <!-- Mobile backdrop -->
        <div
            id="sidebar-backdrop"
            class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none peer-checked:opacity-100 peer-checked:pointer-events-auto transition-opacity duration-300 lg:hidden"
            aria-hidden="true"
        ></div>

        <!-- Sidebar -->
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-40 w-72 bg-primary-800 border-r border-white/10 shadow-xl transition-transform duration-300 ease-in-out flex flex-col -translate-x-full peer-checked:translate-x-0 lg:translate-x-0 lg:static lg:w-72"
            aria-label="Main navigation"
        >
            <x-layout.sidebar />
        </aside>

        <!-- Main content wrapper -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <x-layout.header title="{!! $title ?? 'Dashboard' !!}">
                {!! $breadcrumbs ?? '' !!}
            </x-layout.header>

            <main id="main-content" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
