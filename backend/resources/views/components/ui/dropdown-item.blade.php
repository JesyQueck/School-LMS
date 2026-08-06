@props(['href' => '#'])

<a href="{{ $href }}" role="menuitem" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white cursor-pointer transition-all duration-200">
    {{ $slot }}
</a>
