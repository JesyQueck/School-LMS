<x-layouts.app title="Edit Student">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.students') }}">Students</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Edit Student</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Edit Student</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Update {{ $student->user->name ?? 'this student' }}'s details.</p>
    </div>

    <x-ui.card>
        <div class="p-6 text-center">
            <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-3">Profile Photo</h3>
            <x-profile-photo-uploader :user="$student->user"
                :form-action="route('admin.students.photo.update', $student)"
                :destroy-action="route('admin.students.photo.destroy', $student)" />
            <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">Set or remove this student's profile photo.</p>
        </div>
    </x-ui.card>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.students.update', $student) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Full Name <span class="text-danger-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $student->user->name ?? '') }}" required
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('name')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email Address (Optional)</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $student->user->email ?? '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="e.g. john@example.com">
                    @error('email')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admission_no" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Admission Number <span class="text-danger-500">*</span></label>
                    <input id="admission_no" name="admission_no" type="text" value="{{ old('admission_no', $student->admission_no ?? '') }}" required
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('admission_no')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="class_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class</label>
                    <select id="class_id" name="class_id"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Unassigned</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="first_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">First Name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $student->first_name ?? '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="e.g. John">
                    @error('first_name')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Last Name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $student->last_name ?? '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="e.g. Doe">
                    @error('last_name')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Phone Number</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $student->user->phone ?? '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="e.g. 08012345678">
                    @error('phone')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Date of Birth</label>
                    <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', isset($student->date_of_birth) ? $student->date_of_birth->format('Y-m-d') : '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('date_of_birth')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Gender</label>
                    <select id="gender" name="gender"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Select gender</option>
                        <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $student->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="parent_email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Parent Email <span class="text-danger-500">*</span></label>
                    <input id="parent_email" name="parent_email" type="email" value="{{ old('parent_email', $student->parents->first()->user->email ?? '') }}" required placeholder="e.g. parent@example.com"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Will auto-create parent account if not found</p>
                    @error('parent_email')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="parent_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Parent Name</label>
                    <input id="parent_name" name="parent_name" type="text" value="{{ old('parent_name', $student->parents->first()->user->name ?? '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="e.g. Mr. Student">
                </div>

                <div>
                    <label for="parent_phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Parent Phone</label>
                    <input id="parent_phone" name="parent_phone" type="tel" value="{{ old('parent_phone', $student->parents->first()->phone ?? $student->parents->first()->user->phone ?? '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="e.g. 08012345678">
                </div>

                <div>
                    <label for="parent_occupation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Parent Occupation</label>
                    <input id="parent_occupation" name="parent_occupation" type="text" value="{{ old('parent_occupation', $student->parents->first()->occupation ?? '') }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="e.g. Engineer">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Password</label>
                    <input id="password" name="password" type="password"
                        class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base placeholder-neutral-400 dark:placeholder-neutral-400 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Leave blank to keep current password">
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Must be at least 8 characters with mixed case, a number, and a symbol. Leave blank to keep the current password.</p>
                </div>
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('admin.students') }}"
                    class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</a>
                <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Save Changes</button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
