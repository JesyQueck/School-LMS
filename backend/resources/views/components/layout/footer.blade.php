<footer class="bg-white dark:bg-dark-surface border-t border-neutral-200 dark:border-dark-border px-4 sm:px-6 lg:px-8 py-3 flex-shrink-0">
    {{ $slot ?? '<p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; ' . date('Y') . ' School LMS. All rights reserved.</p>' }}
</footer>
