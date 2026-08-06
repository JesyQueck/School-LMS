<div class="flex-1 flex flex-col overflow-y-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-linear-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-xl font-bold shadow-lg">
                {{ substr($title, 0, 2) }}
            </div>
            <div>
                <div class="text-lg font-bold text-neutral-900 dark:text-white">{{ $title }}</div>
                @if(auth()->check())
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ auth()->user()->name }}</span>
                @endif
            </div>
        </div>
        <label for="sidebar-menu-checkbox" class="lg:hidden cursor-pointer text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </label>
    </div>
    <nav class="flex-1 p-4">
        {{ $slot }}
    </nav>
</div>