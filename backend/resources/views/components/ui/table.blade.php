@props([
    'striped' => false,
    'hoverable' => true,
])

<div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-dark-border">
    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
        {{ $slot }}
    </table>
</div>
