<x-layouts.app title="Salary Components - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Salary Components</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Salary Components</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage allowances and deductions used in salary profiles.</p>
    </div>

    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex-1 min-w-0">
            <form method="GET" action="{{ route('admin.payroll.components.index') }}" class="flex flex-col sm:flex-row gap-3">
                <select name="type" class="rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Types</option>
                    @foreach($types as $type => $label)
                        <option value="{{ $type }}" {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm text-sm transition-colors">Filter</button>
                <a href="{{ route('admin.payroll.components.index') }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm px-2 py-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Reset</a>
            </form>
        </div>
        <a href="{{ route('admin.payroll.components.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm text-sm transition-colors flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Component
        </a>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    <x-ui.card class="mb-6" elevated>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Component Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Calculation</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Default Value</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Active</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($components as $component)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $component->name }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($component->component_type) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($component->calculation_type) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($component->default_value, 2) }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full {{ $component->is_active ? 'bg-success-100 dark:bg-success-900/30' : 'bg-neutral-100 dark:bg-neutral-800/30' }} px-2 py-0.5 text-xs font-medium {{ $component->is_active ? 'text-success-700 dark:text-success-300' : 'text-neutral-700 dark:text-neutral-300' }}">{{ $component->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.payroll.components.show', $component) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View</a>
                                    <a href="{{ route('admin.payroll.components.edit', $component) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.payroll.components.destroy', $component) }}" class="inline" onsubmit="return confirm('Delete this component? This cannot be undone if used in profiles.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-danger-600 dark:text-danger-400 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No salary components found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-dark-border">
            {{ $components->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>
