@props(['quote' => '', 'author' => '', 'role' => '', 'avatar' => null])

<div class="bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border p-7 shadow-premium card-lift hover:shadow-premium-lg">
    <div class="flex items-center gap-1 mb-5">
        @for($i = 0; $i < 5; $i++)
            <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        @endfor
    </div>
    <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed mb-6 text-[15px]">{{ $quote }}</p>
    <div class="flex items-center gap-3 pt-5 border-t-2 border-neutral-100 dark:border-neutral-800">
        <div class="h-11 w-11 rounded-full bg-linear-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center font-bold text-sm shadow-md">
            {{ $avatar ?: substr($author, 0, 1) }}
        </div>
        <div>
            <p class="text-sm font-bold text-neutral-900 dark:text-white">{{ $author }}</p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $role }}</p>
        </div>
    </div>
</div>
