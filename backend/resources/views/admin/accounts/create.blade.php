<x-layouts.app title="Create Account">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/accounts">Accounts</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Create Account</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Create Account</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Create user accounts for teachers, students, and parents.</p>
    </div>

    @if(session('new_account_credentials'))
        @php
            $cred = session('new_account_credentials');
        @endphp
        <x-ui.alert type="info" class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <strong>{{ ucfirst($cred['role']) }}</strong> account created for {{ $cred['name'] }} ({{ $cred['email'] }}).
                    Temporary password: <span class="font-mono font-medium">{{ $cred['password'] }}</span>.
                    The user must change their password on first login. This credential is also saved in the <a href="{{ route('admin.students.import.credentials.view') }}" class="underline font-medium">credentials manager</a>.
                </div>
            </div>
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">New Account</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Enter the details below to create a new account.</p>
                </div>
                <form method="POST" action="{{ route('admin.accounts.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Account Type <span class="text-danger-500">*</span></label>
                        <select id="type" name="type" required onchange="toggleFields()" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none">
                            <option value="">Select account type</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                            <option value="parent">Parent</option>
                        </select>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Full Name <span class="text-danger-500">*</span></label>
                        <input id="name" name="name" type="text" required placeholder="e.g. John Doe" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email Address <span class="text-danger-500">*</span></label>
                        <input id="email" name="email" type="email" required placeholder="e.g. john@example.com" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Phone Number</label>
                        <input id="phone" name="phone" type="tel" placeholder="e.g. 08012345678" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div id="teacher-fields" class="hidden">
                        <label for="qualification" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Qualification</label>
                        <input id="qualification" name="qualification" type="text" placeholder="e.g. B.Ed Mathematics" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                        <div id="student-fields" class="hidden">
                            <label for="class_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class</label>
                            <select id="class_id" name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none">
                                <option value="">Assign to class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>

                            <label for="first_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mt-2 mb-1">First Name</label>
                            <input id="first_name" name="first_name" type="text" placeholder="e.g. John" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">

                            <label for="last_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mt-2 mb-1">Last Name</label>
                            <input id="last_name" name="last_name" type="text" placeholder="e.g. Doe" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">

                        <label for="date_of_birth" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mt-2 mb-1">Date of Birth</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">

                        <label for="gender" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mt-2 mb-1">Gender</label>
                        <select id="gender" name="gender" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none">
                            <option value="">Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>

                        <label for="parent_email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mt-2 mb-1">Parent Email (Optional)</label>
                        <input id="parent_email" name="parent_email" type="email" placeholder="e.g. parent@example.com" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">

                        <div id="parent-details" class="hidden">
                            <input type="hidden" name="parent_name" id="parent_name">
                            <input type="hidden" name="parent_phone" id="parent_phone">
                            <input type="hidden" name="parent_occupation" id="parent_occupation">
                        </div>
                    </div>

                    <div id="parent-fields" class="hidden">
                        <label for="occupation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Occupation</label>
                        <input id="occupation" name="occupation" type="text" placeholder="e.g. Engineer" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Create Account</button>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-5">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Workflow Information</h3>
                </div>
                <div class="p-6">
                    <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Account Creation Process</h4>
                    <ul class="space-y-3 text-sm text-neutral-600 dark:text-neutral-400">
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-primary-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>A user account is created with a temporary password</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-primary-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>User must change password on first login</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-primary-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Profile is linked to the user account</span>
                        </li>
                    </ul>

                    <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mt-4 mb-3">Account Types</h4>
                    <div class="space-y-2 text-xs text-neutral-500 dark:text-neutral-400">
                        <div class="border-l-2 border-neutral-200 dark:border-dark-border pl-3">
                            <strong>Teacher:</strong> Creates teacher record with employee ID and qualification
                        </div>
                        <div class="border-l-2 border-neutral-200 dark:border-dark-border pl-3">
                            <strong>Student:</strong> Creates student record with admission number and class assignment
                        </div>
                        <div class="border-l-2 border-neutral-200 dark:border-dark-border pl-3">
                            <strong>Parent:</strong> Creates parent account that can link to multiple students
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <script>
        function toggleFields() {
            const type = document.getElementById('type').value;
            document.getElementById('teacher-fields').classList.toggle('hidden', type !== 'teacher');
            document.getElementById('student-fields').classList.toggle('hidden', type !== 'student');
            document.getElementById('parent-fields').classList.toggle('hidden', type !== 'parent');
        }
    </script>
</x-layouts.app>