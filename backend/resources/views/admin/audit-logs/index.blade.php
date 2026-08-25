<x-layouts.app title="Audit Logs">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Audit Logs</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Audit Logs</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">A complete record of actions performed in the system.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.card>
        <form method="GET" class="p-4 grid grid-cols-1 sm:grid-cols-4 gap-3 border-b border-neutral-200 dark:border-dark-border">
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Action</label>
                <input name="action" value="{{ request('action') }}" placeholder="e.g. payment.created" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">User ID</label>
                <input name="user_id" value="{{ request('user_id') }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Target</label>
                <input name="target_model" value="{{ request('target_model') }}" placeholder="App\Models\Student" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            </div>
            <div class="sm:col-span-4 flex gap-2">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-neutral-500 dark:text-neutral-400 hover:underline self-center">Reset</a>
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($logs as $log)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $log->action }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $log->target_model ? class_basename($log->target_model) . ' #' . $log->target_id : '—' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-neutral-500 dark:text-neutral-400">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $logs->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>
