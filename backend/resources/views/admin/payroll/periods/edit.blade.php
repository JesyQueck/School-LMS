<x-layouts.app title="Edit Payroll Period - Payroll">
    <div class="space-y-6">
        <div>
            <x-ui.breadcrumbs>
                <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
                <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
                <x-ui.breadcrumb-item href="{{ route('admin.payroll.periods.index') }}">Payroll Periods</x-ui.breadcrumb-item>
                <x-ui.breadcrumb-item href="{{ route('admin.payroll.periods.show', $period) }}">{{ $period->name }}</x-ui.breadcrumb-item>
                <x-ui.breadcrumb-item active>Edit</x-ui.breadcrumb-item>
            </x-ui.breadcrumbs>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Edit Payroll Period</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Update the dates, payment date and status for {{ $period->name }}.
                </p>
            </div>
            <a href="{{ route('admin.payroll.periods.show', $period) }}"
               class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-sm font-semibold text-neutral-700 hover:bg-neutral-50 dark:border-dark-border dark:bg-dark-surface dark:text-neutral-200 dark:hover:bg-neutral-800">
                Cancel and Return
            </a>
        </div>

        @if($errors->any())
            <x-ui.alert variant="danger">
                <div>
                    <p class="font-semibold">Please correct the following:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </x-ui.alert>
        @endif

        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-dark-border dark:bg-dark-surface">
            <div class="border-b border-neutral-200 px-6 py-5 dark:border-dark-border">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Period Details</h2>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Fields marked with * are required.</p>
            </div>

            <form method="POST" action="{{ route('admin.payroll.periods.update', $period) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                            Period Name <span class="text-red-600">*</span>
                        </label>
                        <input id="name" name="name" type="text" required
                               value="{{ old('name', $period->name) }}"
                               class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 placeholder-neutral-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-dark-border dark:bg-neutral-900 dark:text-white">
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                            Status <span class="text-red-600">*</span>
                        </label>
                        <select id="status" name="status" required
                                class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-dark-border dark:bg-neutral-900 dark:text-white">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ old('status', $period->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                            Start Date <span class="text-red-600">*</span>
                        </label>
                        <input id="start_date" name="start_date" type="date" required
                               value="{{ old('start_date', $period->start_date->format('Y-m-d')) }}"
                               class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-dark-border dark:bg-neutral-900 dark:text-white">
                    </div>

                    <div>
                        <label for="end_date" class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                            End Date <span class="text-red-600">*</span>
                        </label>
                        <input id="end_date" name="end_date" type="date" required
                               value="{{ old('end_date', $period->end_date->format('Y-m-d')) }}"
                               class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-dark-border dark:bg-neutral-900 dark:text-white">
                    </div>

                    <div>
                        <label for="pay_date" class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                            Pay Date <span class="text-red-600">*</span>
                        </label>
                        <input id="pay_date" name="pay_date" type="date" required
                               value="{{ old('pay_date', $period->pay_date->format('Y-m-d')) }}"
                               class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-dark-border dark:bg-neutral-900 dark:text-white">
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-neutral-200 pt-5 dark:border-dark-border">
                    <a href="{{ route('admin.payroll.periods.show', $period) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-5 py-2.5 text-sm font-semibold text-neutral-700 hover:bg-neutral-50 dark:border-dark-border dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>