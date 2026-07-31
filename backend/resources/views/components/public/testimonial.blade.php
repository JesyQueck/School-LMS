@props(['quote' => '', 'author' => '', 'role' => '', 'avatar' => null])

<div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm">
    <div class="flex items-start gap-1 mb-4">
        <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4.995v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4.995v10h-9.983z"/></svg>
        <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4.995v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4.995v10h-9.983z"/></svg>
    </div>
    <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed mb-6">{{ $quote }}</p>
    <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-sm">
            {{ $avatar ?: substr($author, 0, 1) }}
        </div>
        <div>
            <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $author }}</p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $role }}</p>
        </div>
    </div>
</div>
