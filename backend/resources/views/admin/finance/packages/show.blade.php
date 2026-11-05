<x-layouts.app title="{{ $package->name }}">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/finance">Finance</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/finance/packages">Fee Packages</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>{{ $package->name }}</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $package->name }}</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $package->description ?? 'No description' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full {{ $package->is_active ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300' }} px-3 py-1 text-sm font-medium">
                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                </span>
                <a href="{{ route('admin.finance.packages.edit', $package) }}" class="px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">Edit</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-ui.stat-card label="Total Amount" :value="'₦' . number_format($package->total, 2)" icon="wallet" compact="true" />
        <x-ui.stat-card label="Components" :value="$package->components->count()" icon="list" compact="true" />
        <x-ui.stat-card label="Assignments" :value="$package->assignments->count()" icon="link" compact="true" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card class="mb-6">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Components</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Component</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @forelse($package->components as $component)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $component->name }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $component->description ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($component->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No components found.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-neutral-900 dark:text-white">Total</td>
                                <td class="px-6 py-3 text-right text-sm font-bold text-neutral-900 dark:text-white">₦{{ number_format($package->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Assignments</h3>
                </div>
                <div class="p-6">
                    @if($package->assignments->isEmpty())
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No assignments yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($package->assignments as $assignment)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 dark:border-dark-border">
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->class->name }} - {{ $assignment->term->name }} ({{ $assignment->academicSession->name }})</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Assigned by {{ $assignment->assignedBy->name }} on {{ $assignment->created_at->format('d M Y') }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full {{ $assignment->is_active ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300' }} px-2 py-0.5 text-xs font-medium">
                                        {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card class="h-full flex flex-col">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Actions</h3>
                </div>
                <div class="p-6 flex-1 flex flex-col gap-4">
                    @if(!$package->assignments()->exists())
                        <a href="{{ route('admin.finance.packages.edit', $package) }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                            <div class="h-8 w-8 rounded-lg bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></div>
                            <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Edit Package</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Modify components</p></div>
                        </a>
                        <button type="button" onclick="confirmDelete()" class="flex items-center gap-3 p-3 rounded-lg border border-danger-200 dark:border-danger-800 hover:border-danger-400 dark:hover:border-danger-600 hover:bg-danger-200 dark:hover:bg-danger-900/10 transition-colors w-full">
                            <div class="h-8 w-8 rounded-lg bg-danger-200 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                            <div><p class="text-sm font-medium text-danger-600 dark:text-danger-400">Delete Package</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Only if no assignments</p></div>
                        </button>
                    @else
                        <div class="p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg">
                            <p class="text-sm text-warning-700 dark:text-warning-300">This package has assignments. Components cannot be edited.</p>
                        </div>
                    @endif

                    <a href="{{ route('admin.finance.packages.assignments') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-success-200 dark:bg-success-900/30 text-success-700 dark:text-success-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg></div>
                        <div><p class="text-sm font-medium text-neutral-900 dark:text-white">Manage Assignments</p><p class="text-xs text-neutral-500 dark:text-neutral-400">Assign to classes/terms</p></div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this fee package? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.finance.packages.destroy", $package) }}';
                form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @endpush
</x-layouts.app>