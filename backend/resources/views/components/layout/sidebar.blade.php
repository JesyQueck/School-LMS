@props(['title' => config('app.name', 'Greenfield Academy')])

<div class="flex items-center justify-between h-16 px-6 border-b border-neutral-200 dark:border-dark-border shrink-0">
    <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-xl bg-linear-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-sm font-bold shadow-md">GA</div>
        <span class="text-lg font-bold text-neutral-900 dark:text-white tracking-tight">{{ $title }}</span>
    </div>
    <label for="sidebar-menu-checkbox" class="lg:hidden p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 transition-colors cursor-pointer" aria-label="Close menu">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </label>
</div>

<nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" aria-label="Sidebar">
    {{ $slot }}
</nav>

<div class="p-4 border-t border-neutral-200 dark:border-dark-border shrink-0">
    <form method="POST" action="/logout">
        @csrf
        <button type="submit" class="flex items-center gap-2.5 w-full px-3 py-2.5 text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:bg-danger-50 dark:hover:bg-danger-900/20 hover:text-danger-600 dark:hover:text-danger-400 rounded-xl transition-all duration-200 focus-visible-ring">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Logout
        </button>
    </form>
</div>
