<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Greenfield Academy') }}</title>
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
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-dark-surface border-r border-neutral-200 dark:border-dark-border transition-transform duration-300 ease-in-out flex flex-col -translate-x-full peer-checked:translate-x-0 lg:translate-x-0 lg:relative"
            aria-label="Sidebar"
        >
            <x-layout.sidebar :title="config('app.name', 'Greenfield Academy')">
                @if(auth()->check())
                    @php $role = auth()->user()->role; @endphp

                    @if($role === 'admin')
                        <x-layout.sidebar-item href="/admin/dashboard" icon="layout-dashboard" label="Dashboard" />
                        <x-layout.sidebar-item href="/admin/accounts" icon="user-check" label="Accounts" />
                        <x-layout.sidebar-item href="/admin/classes" icon="school" label="Classes" />
                        <x-layout.sidebar-item href="/admin/teachers" icon="graduation-cap" label="Teachers" />
                        <x-layout.sidebar-item href="/admin/students" icon="users" label="Students" />
                        <x-layout.sidebar-item href="/admin/results" icon="clipboard-list" label="Results" />
                        <x-layout.sidebar-item href="/admin/finance" icon="wallet" label="Finance" />
                        <x-layout.sidebar-item href="/admin/report-cards" icon="file-text" label="Report Cards" />
                        <x-layout.sidebar-item href="/admin/assignments" icon="book-open" label="Assignments" />
                        <x-layout.sidebar-item href="/admin/academic" icon="calendar" label="Academic" />
                        <x-layout.sidebar-item href="/logout" icon="log-out" label="Logout" method="POST" />
                    @elseif($role === 'teacher')
                        <x-teacher-nav />
                    @elseif($role === 'parent')
                        <x-layout.sidebar-item href="/parent/dashboard" icon="layout-dashboard" label="Dashboard" />
                        <x-layout.sidebar-item href="/parent/timetable" icon="calendar" label="Timetable" />
                        <x-layout.sidebar-item href="/parent/announcements" icon="megaphone" label="Announcements" />
                        <x-layout.sidebar-item href="/logout" icon="log-out" label="Logout" method="POST" />
                    @elseif($role === 'student')
                        <x-layout.sidebar-item href="/student/dashboard" icon="layout-dashboard" label="Dashboard" />
                        <x-layout.sidebar-item href="/student/results" icon="clipboard-list" label="Results" />
                        <x-layout.sidebar-item href="/student/attendance" icon="calendar" label="Attendance" />
                        <x-layout.sidebar-item href="/student/fees" icon="wallet" label="Fees" />
                        <x-layout.sidebar-item href="/student/report-cards" icon="file-text" label="Report Cards" />
                        <x-layout.sidebar-item href="/student/announcements" icon="megaphone" label="Announcements" />
                        <x-layout.sidebar-item href="/logout" icon="log-out" label="Logout" method="POST" />
                    @endif
                @endif
            </x-layout.sidebar>
        </aside>

        <!-- Main content wrapper -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <x-layout.header title="{!! $title ?? 'Dashboard' !!}">
                {!! $breadcrumbs ?? '' !!}
            </x-layout.header>

            <main id="main-content" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

            <x-layout.footer>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; {{ date('Y') }} {{ config('app.name', 'Greenfield Academy') }}. All rights reserved.</p>
            </x-layout.footer>
        </div>
    </div>
</body>
</html>
