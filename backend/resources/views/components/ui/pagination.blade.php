@props(['pages' => []])

<div class="flex items-center justify-between">
    <nav class="flex items-center gap-2 text-sm" aria-label="Pagination">
        @if($pages['prev'])
            <a href="{{ $pages['prev'] }}" class="px-3 py-1.5 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">Previous</a>
        @else
            <span class="px-3 py-1.5 rounded-md text-sm font-medium text-neutral-400 dark:text-neutral-600 cursor-not-allowed">Previous</span>
        @endif
        <div class="flex items-center gap-1">
            @foreach($pages['links'] as $page)
                @if($page['active'])
                    <span class="px-3 py-1.5 rounded-md text-sm font-medium bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">{{ $page['label'] }}</span>
                @else
                    <a href="{{ $page['url'] }}" class="px-3 py-1.5 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">{{ $page['label'] }}</a>
                @endif
            @endforeach
        </div>
        @if($pages['next'])
            <a href="{{ $pages['next'] }}" class="px-3 py-1.5 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">Next</a>
        @else
            <span class="px-3 py-1.5 rounded-md text-sm font-medium text-neutral-400 dark:text-neutral-600 cursor-not-allowed">Next</span>
        @endif
    </nav>
    <p class="text-sm text-neutral-500 dark:text-neutral-400">Showing {{ $pages['from'] }} to {{ $pages['to'] }} of {{ $pages['total'] }} results</p>
</div>
