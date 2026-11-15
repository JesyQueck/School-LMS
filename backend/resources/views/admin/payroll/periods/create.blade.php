<x-layouts.app title="Create Payroll Period - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.periods.index') }}">Payroll Periods</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Create Period</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Create Payroll Period</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Define a new payroll period with start, end, and pay dates.</p>
    </div>

    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <div class="flex flex-col gap-2">
                <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">Please fix the following errors:</h4>
                <ul class="list-disc list-inside text-sm text-danger-700 dark:text-danger-400 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </x-ui.alert>
    @endif

    <x-ui.card class="mb-6" elevated>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Period Details</h3>
        </div>
        <form method="POST" action="{{ route('admin.payroll.periods.store') }}" class="p-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Period Name <span class="text-danger-500">*</span></label>
                    <input name="name" type="text" required value="{{ old('name') }}" placeholder="e.g. January 2026" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Start Date <span class="text-danger-500">*</span></label>
                    <input name="start_date" type="date" required value="{{ old('start_date') }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">End Date <span class="text-danger-500">*</span></label>
                    <input name="end_date" type="date" required value="{{ old('end_date') }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Pay Date <span class="text-danger-500">*</span></label>
                    <input name="pay_date" type="date" required value="{{ old('pay_date') }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Status <span class="text-danger-500">*</span></label>
                    <select name="status" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ old('status', 'draft') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('admin.payroll.periods.index') }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm font-medium px-4 py-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Cancel</a>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2.5 rounded-xl shadow-sm text-sm transition-colors">Create Period</button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
