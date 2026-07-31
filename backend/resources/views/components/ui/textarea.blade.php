@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'error' => '',
    'rows' => 4,
])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ $label }}
            @if($required)<span class="text-danger-500">*</span>@endif
        </label>
    @endif
    <textarea 
        name="{{ $name }}" 
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-vertical ' . ($error ? 'border-danger-500 focus:ring-danger-500' : '')]) }}
    >{{ $slot }}</textarea>
    @if($error)
        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $error }}</p>
    @endif
</div>
