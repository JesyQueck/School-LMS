<div class="relative" x-data="{ open = false }">
    <button @click="open = !open" class="relative p-2 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white focus:outline-none transition-colors">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l1-1v-1.05a3 3 0 00-2.83-2.92l-1.45-.29A4.33 4.33 0 0012 12.5V10a4 4 0 10-8 0v2.5a4.33 4.33 0 00-2.17 1.16V15l1 1h5"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18a3 3 0 11-6 0v-1a3 3 0 013-3h3z"></path>
        </svg>
        @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-danger-500 flex items-center justify-center text-[10px] font-bold text-white">
            {{ $unreadCount }}
        </span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border rounded-xl shadow-lg z-50">
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border flex justify-between items-center">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Notifications</h3>
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                    Mark all read
                </button>
            </form>
            @endif
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications as $notification)
            <div class="p-3 border-b border-neutral-100 dark:border-neutral-800 last:border-b-0 {{ $notification->is_read ? '' : 'bg-primary-50/50 dark:bg-primary-900/10' }}">
                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $notification->title }}</p>
                <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 line-clamp-2">{{ $notification->message }}</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
            @empty
            <div class="p-4 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No notifications yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>