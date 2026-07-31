@props(['active' => false])

@php
    $states = [
        'active' => 'text-primary-600 dark:text-primary-400 border-primary-600 dark:border-primary-400',
        'inactive' => 'text-neutral-500 dark:text-neutral-400 border-transparent hover:text-neutral-700 dark:hover:text-neutral-300 hover:border-neutral-300 dark:hover:border-neutral-600',
    ];
@endphp

<button 
    type="button" 
    {{ $attributes->merge(['class' => 'px-4 py-2 text-sm font-medium border-b-2 transition-colors ' . ($active ? $states['active'] : $states['inactive'])]) }}
    role="tab"
    {{ $attributes->get('aria-selected', $active ? 'true' : 'false') }}
>
    {{ $slot }}
</button>
