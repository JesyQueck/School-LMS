@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'error' => '',
])

<div class="flex items-center gap-3">
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'h-5 w-5 border-2 border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-dark-surface dark:focus:ring-offset-dark-bg']) }}
    >
    @if($label)
        <label for="{{ $name }}" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 cursor-pointer">
            {{ $label }}
            @if($required)<span class="text-danger-500 ml-0.5">*</span>@endif
        </label>
    @endif
</div>
