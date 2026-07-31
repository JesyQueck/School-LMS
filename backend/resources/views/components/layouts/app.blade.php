<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <x-layout.skip-link />

    <div class="flex h-screen overflow-hidden bg-neutral-50 dark:bg-dark-bg">
        <!-- Mobile backdrop -->
        <div 
            x-show="$store.sidebar.open" 
            x-cloak
            @click="$store.sidebar.close()"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            aria-hidden="true"
        ></div>

        <!-- Sidebar -->
        <aside 
            :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-dark-surface border-r border-neutral-200 dark:border-dark-border transition-transform duration-300 lg:translate-x-0 lg:static lg:block flex flex-col"
            aria-label="Sidebar"
            x-cloak
        >
            <x-layout.sidebar :title="config('app.name', 'School LMS')">
                @if(auth()->check())
                    @php $role = auth()->user()->role; @endphp

                    @if($role === 'admin')
                        <x-layout.sidebar-item href="/admin/dashboard" icon="layout-dashboard" label="Dashboard" />
                        <x-layout.sidebar-item href="/admin/classes" icon="school" label="Classes" />
                        <x-layout.sidebar-item href="/admin/teachers" icon="graduation-cap" label="Teachers" />
                        <x-layout.sidebar-item href="/admin/students" icon="users" label="Students" />
                        <x-layout.sidebar-item href="/admin/results" icon="clipboard-list" label="Results" />
                        <x-layout.sidebar-item href="/admin/finance" icon="wallet" label="Finance" />
                        <x-layout.sidebar-item href="/admin/report-cards" icon="file-text" label="Report Cards" />
                        <x-layout.sidebar-item href="/admin/assignments" icon="book-open" label="Assignments" />
                        <x-layout.sidebar-item href="/admin/academic" icon="calendar" label="Academic" />
                    @elseif($role === 'teacher')
                        <x-layout.sidebar-item href="/teacher/dashboard" icon="layout-dashboard" label="Dashboard" />
                    @elseif($role === 'parent')
                        <x-layout.sidebar-item href="/parent/dashboard" icon="layout-dashboard" label="Dashboard" />
                        <x-layout.sidebar-item href="/parent/announcements" icon="megaphone" label="Announcements" />
                    @elseif($role === 'student')
                        <x-layout.sidebar-item href="/student/dashboard" icon="layout-dashboard" label="Dashboard" />
                        <x-layout.sidebar-item href="/student/results" icon="clipboard-list" label="Results" />
                        <x-layout.sidebar-item href="/student/attendance" icon="calendar-check" label="Attendance" />
                        <x-layout.sidebar-item href="/student/fees" icon="wallet" label="Fees" />
                        <x-layout.sidebar-item href="/student/report-cards" icon="file-text" label="Report Cards" />
                        <x-layout.sidebar-item href="/student/announcements" icon="megaphone" label="Announcements" />
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
                <p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; {{ date('Y') }} School LMS. All rights reserved.</p>
            </x-layout.footer>
        </div>
    </div>
</body>
</html>
