<x-layouts.app title="Audit Logs">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Audit Logs</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Audit Logs</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">A complete record of actions performed in the system.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <div class="flex flex-col gap-2">
                <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">Please fix the following errors:</h4>
                <ul class="list-disc list-inside text-sm text-danger-700 dark:text-danger-400 space-y-1">
                    <li>{{ $errors->first() }}</li>
                </ul>
            </div>
        </x-ui.alert>
    @endif

    <x-ui.card>
        <form method="GET" class="p-4 grid grid-cols-1 sm:grid-cols-4 gap-3 border-b border-neutral-200 dark:border-dark-border">
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">What happened</label>
                <input name="action" value="{{ request('action') }}" placeholder="e.g. payment, timetable" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">User ID</label>
                <input name="user_id" value="{{ request('user_id') }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Record type</label>
                <input name="target_model" value="{{ request('target_model') }}" placeholder="e.g. Student, Teacher" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
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
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">What happened</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Record</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($logs as $log)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $log->user->name ?? 'System' }} — {{ $actionLabels[$log->action] ?? $log->action }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $log->target_model ? class_basename($log->target_model) : '—' }}</td>
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