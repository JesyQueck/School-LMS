@props(['title' => ''])

<div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
    @if($title)
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $title }}</h3>
    @endif
    <button @click="$wire.set('showModal', false)" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" aria-label="Close modal">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
