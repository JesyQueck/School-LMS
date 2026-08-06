@props([
    'striped' => false,
    'hoverable' => true,
])

<div class="overflow-x-auto rounded-2xl border-2 border-neutral-200 dark:border-dark-border shadow-premium">
    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
        {{ $slot }}
    </table>
</div>
