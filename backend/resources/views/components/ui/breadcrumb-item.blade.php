@props(['href' => '#', 'active' => false])

<li class="flex items-center">
    @if($active)
        <span class="text-neutral-900 dark:text-white font-medium" aria-current="page">{{ $slot }}</span>
    @else
        <a href="{{ $href }}" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors">{{ $slot }}</a>
    @endif
</li>
