<x-layouts.app title="Edit Employee - Payroll">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.index') }}">Payroll</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.payroll.employees.index') }}">Employees</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Edit Employee</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Edit Employee</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Edit payroll employee details.</p>
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
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Edit Employee Details</h3>
        </div>
        <form method="POST" action="{{ route('admin.payroll.employees.update', $employee) }}" class="p-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Employee Number <span class="text-danger-500">*</span></label>
                    <input name="employee_number" type="text" required value="{{ old('employee_number', $employee->employee_number) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Employee Name <span class="text-danger-500">*</span></label>
                    <input name="name" type="text" maxlength="255"
                        value="{{ old('name', $employee->name ?: ($employee->user?->name ?? '')) }}"
                        placeholder="Enter employee full name"
                        class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Required when no user account is linked.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Linked User</label>
                    <select name="user_id" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">No User Linked</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Employment Type <span class="text-danger-500">*</span></label>
                    <select name="employment_type" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($employmentTypes as $type)
                            <option value="{{ $type }}" {{ old('employment_type', $employee->employment_type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Employment Status <span class="text-danger-500">*</span></label>
                    <select name="employment_status" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ old('employment_status', $employee->employment_status) == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Department</label>
                    <input name="department" type="text" value="{{ old('department', $employee->department) }}" placeholder="e.g. Administration" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Designation</label>
                    <input name="designation" type="text" value="{{ old('designation', $employee->designation) }}" placeholder="e.g. School Clerk" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Date Employed <span class="text-danger-500">*</span></label>
                    <input name="date_employed" type="date" required value="{{ old('date_employed', $employee->date_employed?->format('Y-m-d') ?? '') }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Date Left</label>
                    <input name="date_left" type="date" value="{{ old('date_left', $employee->date_left?->format('Y-m-d') ?? '') }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="mt-6 border-t border-neutral-200 dark:border-dark-border pt-6">
                <h4 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-4">Bank Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Bank Name</label>
                        <input name="bank_name" type="text" value="{{ old('bank_name', $employee->bank_name) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Account Number</label>
                        <input name="bank_account_number" type="text" value="{{ old('bank_account_number', $employee->bank_account_number) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Account Name</label>
                        <input name="bank_account_name" type="text" value="{{ old('bank_account_name', $employee->bank_account_name) }}" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('admin.payroll.employees.show', $employee) }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-sm font-medium px-4 py-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/50">Cancel</a>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2.5 rounded-xl shadow-sm text-sm transition-colors">Save Changes</button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
