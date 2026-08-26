<x-layouts.app title="Profile">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/student/dashboard">Student</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Profile</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Profile</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Your academic and personal information.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <x-ui.card>
                <div class="p-6 text-center">
                    <x-profile-photo-uploader :editable="false" />
                    <h2 class="mt-4 text-xl font-bold text-neutral-900 dark:text-white">{{ $student->full_name ?? '---' }}</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $student->schoolClass->name ?? 'N/A' }}</p>
                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">{{ $student->admission_no ?? 'N/A' }}</p>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Personal Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Full Name</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Admission No</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->admission_no ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Class</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->schoolClass->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Gender</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->gender ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Date of Birth</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Blood Group</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->blood_group ?? '---' }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Guardian Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Guardian Name</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->guardian_name ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Phone</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->guardian_phone ?? '---' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Address</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->address ?? '---' }}</p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('student.dashboard') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
            ← Back to Dashboard
        </a>
    </div>
</x-layouts.app>