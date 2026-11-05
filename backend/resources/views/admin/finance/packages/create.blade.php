<x-layouts.app title="Create Fee Package">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/finance">Finance</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/finance/packages">Fee Packages</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Create Package</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Create Fee Package</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Define a new fee package with its components.</p>
    </div>

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
        <form method="POST" action="{{ route('admin.finance.packages.store') }}" class="p-6 space-y-6" id="package-form">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="lg:col-span-1">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Package Name <span class="text-danger-500">*</span></label>
                            <input name="name" type="text" required value="{{ old('name') }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Description</label>
                            <textarea name="description" rows="3" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">{{ old('description') }}</textarea>
                        </div>
                        <div class="flex items-center">
                            <input name="is_active" type="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
                            <label class="ml-2 text-sm font-medium text-neutral-700 dark:text-neutral-300">Active</label>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Components</h3>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400" id="total-amount">Total: ₦0.00</span>
                        </div>
                        <div id="components-container" class="space-y-3">
                            {{-- Components added via JavaScript --}}
                        </div>
                        <button type="button" onclick="addComponent()" class="w-full mt-3 px-3 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">
                            + Add Component
                        </button>
                    </div>
                </div>
            </div>

            <div class="border-t border-neutral-200 dark:border-dark-border pt-6">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.finance.packages.index') }}" class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:underline">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-sm">Create Package</button>
                </div>
            </div>
        </form>
    </x-ui.card>

    @push('scripts')
    <script>
        let componentIndex = 0;

        function addComponent(data = {}) {
            const container = document.getElementById('components-container');
            const div = document.createElement('div');
            div.className = 'component-item p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-dark-border space-y-3';
            div.innerHTML = `
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-neutral-900 dark:text-white">Component #${componentIndex + 1}</h4>
                    <button type="button" onclick="this.closest('.component-item').remove(); updateTotal();" class="text-danger-600 dark:text-danger-400 hover:underline text-sm">Remove</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Name <span class="text-danger-500">*</span></label>
                        <input name="components[${componentIndex}][name]" type="text" required value="${data.name ?? ''}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" oninput="updateTotal()">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Amount (₦) <span class="text-danger-500">*</span></label>
                        <input name="components[${componentIndex}][amount]" type="number" step="0.01" min="0.01" required value="${data.amount ?? ''}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" oninput="updateTotal()">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Description</label>
                        <input name="components[${componentIndex}][description]" type="text" value="${data.description ?? ''}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                </div>
            `;
            container.appendChild(div);
            componentIndex++;
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('input[name^="components["][name$="[amount]"]').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('total-amount').textContent = 'Total: ₦' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Add first component on load
        document.addEventListener('DOMContentLoaded', () => {
            addComponent();
        });
    </script>
    @endpush
</x-layouts.app>