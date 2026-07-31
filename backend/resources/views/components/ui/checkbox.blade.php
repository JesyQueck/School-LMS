@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'error' => '',
])

<div class="flex items-center gap-2">
    <input 
        type="checkbox" 
        name="{{ $name }}" 
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500 dark:bg-dark-surface']) }}
    >
    @if($label)
        <label for="{{ $name }}" class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ $label }}
            @if($required)<span class="text-danger-500">*</span>@endif
        </label>
    @endif
</div>
