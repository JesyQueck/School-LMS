<x-layouts.app title="Announcements">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Announcements</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-2">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Announcements</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage school-wide announcements.</p>
            </div>
            <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Announcement
            </a>
        </div>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">All Announcements</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $announcements->total() }} announcement{{ $announcements->total() !== 1 ? 's' : '' }}.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Target</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Public</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Created By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($announcements as $announcement)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $announcement->title }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($announcement->target_role) }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                                @if($announcement->show_on_website)
                                    <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2 py-0.5 text-xs font-medium text-success-800 dark:text-success-200">Yes</span>
                                @else
                                    <span class="text-neutral-400 dark:text-neutral-600">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $announcement->createdBy->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $announcement->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.231V1a1 1 0 011.707-.707l2.5 2.5a1 1 0 01-1.414 1.414L14 3.414V9m-3 3h8a1 1 0 110 2H7a1 1 0 01-1-1V9.414l-1.293-1.293A1 1 0 017 7v5z"/></svg>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No announcements found.</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Create your first announcement using the button above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 dark:border-dark-border">
            {{ $announcements->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>
