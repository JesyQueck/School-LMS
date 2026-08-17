<footer class="bg-white dark:bg-dark-surface border-t border-neutral-200 dark:border-dark-border px-4 sm:px-6 lg:px-8 py-4 flex-shrink-0">
    {{ $slot ?? '<p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; ' . date('Y') . ' Greenfield Academy. All rights reserved.</p>' }}
</footer>
