<x-layouts.app title="Profile">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('student.dashboard') }}">Dashboard</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Profile</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
    </div>

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Profile</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View your personal information.</p>
    </div>

    <x-ui.card>
        <div class="p-6">
            @if($student && $student->user)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Full Name</h3>
                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $student->full_name ?? $student->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Admission No</h3>
                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $student->admission_no ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Class</h3>
                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $student->class->name ?? 'Unassigned' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Date of Birth</h3>
                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Gender</h3>
                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $student->gender ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Email</h3>
                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $student->user->email ?? 'N/A' }}</p>
                </div>
            </div>
            @else
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Student profile not available.</p>
            @endif
        </div>
    </x-ui.card>
</x-layouts.app>