@props([
    'hoverable' => true,
])

<thead class="bg-neutral-50 dark:bg-dark-surface">
    <tr>
        {{ $slot }}
    </tr>
</thead>
