@props(['title' => '', 'category' => '', 'image' => null, 'href' => '#'])

<div class="group relative aspect-square rounded-xl overflow-hidden bg-neutral-100 dark:bg-neutral-800 cursor-pointer">
    <div class="absolute inset-0 bg-gradient-to-br from-primary-200 to-accent-200 dark:from-primary-800/50 dark:to-accent-800/50 flex items-center justify-center">
        <svg class="h-12 w-12 text-primary-400 dark:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-2 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
        <p class="text-sm font-medium text-white">{{ $title }}</p>
        @if($category)
            <p class="text-xs text-white/70">{{ $category }}</p>
        @endif
    </div>
</div>
