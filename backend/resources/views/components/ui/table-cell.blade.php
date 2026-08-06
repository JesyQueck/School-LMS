@props([
    'scope' => 'row',
])

<td scope="{{ $scope }}" {{ $attributes->merge(['class' => 'px-4 sm:px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400']) }}>
    {{ $slot }}
</td>
