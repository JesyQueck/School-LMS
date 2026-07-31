@props([
    'scope' => 'row',
])

<th scope="{{ $scope }}" {{ $attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider']) }}>
    {{ $slot }}
</th>
