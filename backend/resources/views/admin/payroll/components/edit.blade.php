<x-layouts.app title="Edit Salary Component - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.components.index') }}">Salary Components</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Edit Component</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Edit Salary Component</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Edit allowance or deduction component.</p>
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
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Edit Component Details</h3>
        </div>
        <form method="POST" action="{{ route('admin.payroll.components.update', $salaryComponent) }}" class="p-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Component Name <span class="text-danger-500">*</span></label>
                    <input name="name" type="text" required value="{{ old('name', $salaryComponent->name) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Component Type <span class="text-danger-500">*</span></label>
                    <select name="component_type" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($types as $type => $label)
                            <option value="{{ $type }}" {{ old('component_type', $salaryComponent->component_type) == $type ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Calculation Type <span class="text-danger-500">*</span></label>
                    <select name="calculation_type" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($calculationTypes as $type => $label)
                            <option value="{{ $type }}" {{ old('calculation_type', $salaryComponent->calculation_type) == $type ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Description</label>
                    <input name="description" type="text" value="{{ old('description', $salaryComponent->description) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Default Value (₦) <span class="text-danger-500">*</span></label>
                    <input name="default_value" type="number" step="0.01" required value="{{ old('default_value', $salaryComponent->default_value) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center mb-2">
                        <input name="is_active" type="checkbox" {{ old('is_active', $salaryComponent->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500 bg-neutral-100 dark:bg-neutral-700">
                        <label class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</label>
                    </div>
                    <div class="flex items-center">
                        <input name="is_taxable" type="checkbox" {{ old('is_taxable', $salaryComponent->is_taxable) ? 'checked' : '' }} class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500 bg-neutral-100 dark:bg-neutral-700">
                        <label class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Taxable</label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('admin.payroll.components.show', $salaryComponent) }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm font-medium px-4 py-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Cancel</a>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2.5 rounded-xl shadow-sm text-sm transition-colors">Save Changes</button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
