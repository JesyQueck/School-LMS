<x-layouts.app title="Payroll Periods - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Payroll Periods</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Payroll Periods</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Create and manage payroll periods.</p>
    </div>

    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex-1 min-w-0">
            <form method="GET" action="{{ route('admin.payroll.periods.index') }}" class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm text-sm transition-colors">Filter</button>
                <a href="{{ route('admin.payroll.periods.index') }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm px-2 py-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Reset</a>
            </form>
        </div>
        <a href="{{ route('admin.payroll.periods.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm text-sm transition-colors flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Period
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

    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <x-ui.card class="mb-6" elevated>
        <div class="p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-neutral-900 dark:text-white">Generate Monthly Periods</h2>
            </div>

            <p class="mb-5 max-w-3xl text-sm leading-5 text-neutral-500 dark:text-neutral-400">
                Generate all twelve monthly payroll periods for a selected year. Existing or overlapping periods will be skipped, and each generated period can be managed independently.
            </p>

            <form id="generate-payroll-periods-form" method="POST" action="{{ route('admin.payroll.periods.generate-year') }}"
                class="grid grid-cols-1 sm:grid-cols-[180px_220px_max-content] items-end gap-4">
                @csrf

                <div class="w-full min-w-0">
                    <label for="payroll-year" class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                        Payroll Year
                    </label>
                    <input id="payroll-year" type="number" name="year" min="2000" max="2100"
                        value="{{ old('year', now()->year) }}" required
                        class="block h-11 w-full rounded-xl border border-neutral-300 bg-white px-4 text-sm text-neutral-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text">
                </div>

                <div class="w-full min-w-0">
                    <label for="payroll-pay-day" class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                        Default Payment Day
                    </label>
                    <select id="payroll-pay-day" name="pay_day" required
                        class="block h-11 w-full rounded-xl border border-neutral-300 bg-white px-4 text-sm text-neutral-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text">
                        @for($day = 1; $day <= 31; $day++)
                            <option value="{{ $day }}" {{ (int) old('pay_day', 27) === $day ? 'selected' : '' }}>{{ $day }}{{ in_array($day % 100, [11, 12, 13]) ? 'th' : match($day % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } }}</option>
                        @endfor
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <button type="submit"
                        class="inline-flex h-11 w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-surface">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Generate 12 Months
                    </button>
                </div>
            </form>
        </div>
    </x-ui.card>
    <x-ui.card class="mb-6" elevated>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Period Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Pay Date</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Records</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($periods as $period)
                        @php
                            $statusConfig = match($period->status) {
                                'draft' => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300'],
                                'processed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300'],
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300'],
                                'locked' => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300'],
                                default => ['bg' => 'bg-neutral-100 dark:bg-neutral-800/30', 'text' => 'text-neutral-700 dark:text-neutral-300'],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $period->name }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $period->start_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $period->end_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $period->pay_date->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ ucfirst($period->status) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">{{ $period->payroll_records_count }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.payroll.periods.show', $period) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View</a>
                                    <a href="{{ route('admin.payroll.periods.edit', $period) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.payroll.periods.destroy', $period) }}" class="inline" onsubmit="return confirm('Delete this period? Records must be removed first.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-danger-600 dark:text-danger-400 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payroll periods found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-dark-border">
            {{ $periods->links() }}
        </div>
    </x-ui.card>

    {{-- Generate Payroll Periods Confirmation Modal --}}
    <div id="generate-periods-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-dark-surface">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Generate Monthly Payroll Periods?</h3>
            <p class="mt-2 text-sm leading-6 text-neutral-600 dark:text-neutral-300">
                This will create up to twelve Draft payroll periods for the selected year.
                Existing or overlapping periods will be skipped. You can edit each generated period individually.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="generate-periods-cancel"
                    class="rounded-xl border border-neutral-300 px-4 py-2.5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50 dark:border-dark-border dark:text-neutral-300 dark:hover:bg-neutral-800">
                    Cancel
                </button>
                <button type="button" id="generate-periods-confirm"
                    class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
                    Generate Periods
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const form = document.getElementById('generate-payroll-periods-form');
            const modal = document.getElementById('generate-periods-modal');
            const cancelButton = document.getElementById('generate-periods-cancel');
            const confirmButton = document.getElementById('generate-periods-confirm');

            if (!form || !modal || !cancelButton || !confirmButton) return;

            let confirmed = false;

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            form.addEventListener('submit', function (event) {
                if (confirmed) return;

                event.preventDefault();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });

            cancelButton.addEventListener('click', closeModal);

            confirmButton.addEventListener('click', function () {
                confirmed = true;
                closeModal();
                form.requestSubmit();
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
    @endpush
</x-layouts.app>
