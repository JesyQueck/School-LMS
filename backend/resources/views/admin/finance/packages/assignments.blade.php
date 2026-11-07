<x-layouts.app title="Package Assignments">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/finance">Finance</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/finance/packages">Fee Packages</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Assignments</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Package Assignments</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Assign fee packages to classes, terms, and sessions.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    {{-- Assign New Package --}}
    <x-ui.card class="mb-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Assign Package</h3>
        </div>
        <form method="POST" action="{{ route('admin.finance.packages.assign') }}" class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    @csrf

    <div>
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Package <span class="text-danger-500">*</span></label>
        <select name="fee_package_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            <option value="">Select package</option>
            @foreach($packages as $package)
                <option value="{{ $package->id }}" {{ old('fee_package_id') == $package->id ? 'selected' : '' }}>{{ $package->name }} (₦{{ number_format($package->total, 2) }})</option>
            @endforeach
        </select>
        @error('fee_package_id')
            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Class <span class="text-danger-500">*</span></label>
        <select name="class_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            <option value="">Select class</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @endforeach
        </select>
        @error('class_id')
            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Term <span class="text-danger-500">*</span></label>
        <select name="term_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            <option value="">Select term</option>
            @foreach($terms as $term)
                <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
            @endforeach
        </select>
        @error('term_id')
            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Academic Session <span class="text-danger-500">*</span></label>
        <select name="academic_session_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            <option value="">Select session</option>
            @foreach($academicSessions as $session)
                <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
            @endforeach
        </select>
        @error('academic_session_id')
            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end">
        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm">Assign</button>
    </div>
</form>
    </x-ui.card>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <form method="GET" class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Package</label>
                <select name="package_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ $filters['package_id'] ?? '' == $package->id ? 'selected' : '' }}>{{ $package->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Class</label>
                <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $filters['class_id'] ?? '' == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Term</label>
                <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $filters['term_id'] ?? '' == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm">Filter</button>
            </div>
        </form>
    </x-ui.card>

    {{-- Assignments List --}}
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Package</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Term / Session</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Assigned By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($assignments as $assignment)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->package->name }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->class->name }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->term->name }} / {{ $assignment->academicSession->name }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->assignedBy->name }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $assignment->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full {{ $assignment->is_active ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300' }} px-2 py-0.5 text-xs font-medium">
                                    {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($assignment->is_active)
                                    <form method="POST" action="{{ route('admin.finance.packages.assignment.deactivate', $assignment) }}" onsubmit="return confirm('Deactivate this assignment? Student fees will remain but no new fees will be generated.')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-danger-600 dark:text-danger-400 hover:underline text-sm">Deactivate</button>
                                    </form>
                                @else
                                    <span class="text-neutral-500 dark:text-neutral-400 text-sm">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No assignments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-dark-border">
            {{ $assignments->links() }}
        </div>
    </x-ui.card>
</x-layouts.app>