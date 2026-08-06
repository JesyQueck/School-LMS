@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'error' => '',
    'options' => [],
])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
            {{ $label }}
            @if($required)<span class="text-danger-500 ml-0.5">*</span>@endif
        </label>
    @endif
    <div class="relative">
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none ' . ($error ? 'border-danger-500 focus:ring-danger-500 focus:border-danger-500' : 'hover:border-neutral-300 dark:hover:border-neutral-600')]) }}
        >
            {{ $slot }}
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500 dark:text-neutral-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </div>
    @if($error)
        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
