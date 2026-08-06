@props(['title' => 'No data available', 'description' => 'There are no items to display at this time.', 'action' => null])

<div class="text-center py-16 px-4">
    <div class="mx-auto w-20 h-20 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-6">
        <svg class="h-10 w-10 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
    </div>
    <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-2">{{ $title }}</h3>
    <p class="text-base text-neutral-600 dark:text-neutral-400 max-w-md mx-auto mb-6">{{ $description }}</p>
    @if($action)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
