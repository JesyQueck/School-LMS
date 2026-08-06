@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'error' => '',
    'helper' => '',
])

@php
    $errorId = $name ? 'error-' . $name : '';
@endphp

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
            {{ $label }}
            @if($required)<span class="text-danger-500 ml-0.5">*</span>@endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 ' . ($error ? 'border-danger-500 focus:ring-danger-500 focus:border-danger-500' : 'hover:border-neutral-300 dark:hover:border-neutral-600')]) }}
    >
    @if($error)
        <p id="{{ $errorId }}" class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $error }}
        </p>
    @endif
    @if($helper)
        <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">{{ $helper }}</p>
    @endif
</div>
