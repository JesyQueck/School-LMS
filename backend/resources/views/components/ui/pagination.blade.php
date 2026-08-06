@props(['pages' => []])

<div class="flex flex-col sm:flex-row items-center justify-between gap-4">
    <nav class="flex items-center gap-2 text-sm" aria-label="Pagination">
        @if($pages['prev'])
            <a href="{{ $pages['prev'] }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-all duration-200">Previous</a>
        @else
            <span class="px-4 py-2 rounded-xl text-sm font-semibold text-neutral-400 dark:text-neutral-600 cursor-not-allowed">Previous</span>
        @endif
        <div class="flex items-center gap-1">
            @foreach($pages['links'] as $page)
                @if($page['active'])
                    <span class="px-4 py-2 rounded-xl text-sm font-bold bg-primary-600 text-white shadow-sm">{{ $page['label'] }}</span>
                @else
                    <a href="{{ $page['url'] }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-all duration-200">{{ $page['label'] }}</a>
                @endif
            @endforeach
        </div>
        @if($pages['next'])
            <a href="{{ $pages['next'] }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-all duration-200">Next</a>
        @else
            <span class="px-4 py-2 rounded-xl text-sm font-semibold text-neutral-400 dark:text-neutral-600 cursor-not-allowed">Next</span>
        @endif
    </nav>
    <p class="text-sm text-neutral-500 dark:text-neutral-400">Showing {{ $pages['from'] }} to {{ $pages['to'] }} of {{ $pages['total'] }} results</p>
</div>
