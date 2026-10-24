@props(['class' => ''])

<hr {{ $attributes->merge(['class' => 'border-neutral-200 dark:border-dark-border ' . $class]) }}>
