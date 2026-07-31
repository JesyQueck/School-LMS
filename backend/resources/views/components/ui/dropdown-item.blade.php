@props(['href' => '#'])

<a href="{{ $href }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-colors">
    {{ $slot }}
</a>
