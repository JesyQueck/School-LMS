@props([
    'scope' => 'row',
])

<td scope="{{ $scope }}" {{ $attributes->merge(['class' => 'px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 whitespace-nowrap']) }}>
    {{ $slot }}
</td>
