@props([
    'scope' => 'row',
])

<th scope="{{ $scope }}" {{ $attributes->merge(['class' => 'px-4 sm:px-6 py-4 text-left text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider']) }}>
    {{ $slot }}
</th>
