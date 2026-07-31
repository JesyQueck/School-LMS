@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'error' => '',
    'options' => [],
])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ $label }}
            @if($required)<span class="text-danger-500">*</span>@endif
        </label>
    @endif
    <div class="relative">
        <select 
            name="{{ $name }}" 
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none ' . ($error ? 'border-danger-500 focus:ring-danger-500' : '')]) }}
        >
            {{ $slot }}
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500 dark:text-neutral-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </div>
    @if($error)
        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $error }}</p>
    @endif
</div>
