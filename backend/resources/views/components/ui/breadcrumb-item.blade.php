@props(['href' => '#', 'active' => false])

<li class="flex items-center">
    @if($active)
        <span class="text-neutral-900 dark:text-white font-semibold" aria-current="page">{{ $slot }}</span>
    @else
        <a href="{{ $href }}" class="text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors">{{ $slot }}</a>
    @endif
</li>
