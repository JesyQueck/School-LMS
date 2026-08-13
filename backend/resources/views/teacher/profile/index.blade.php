<x-layouts.app title="My Profile">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>My Profile</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Profile</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Your personal and professional information.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <x-ui.card>
            <div class="p-6 text-center">
                <div class="h-20 w-20 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-2xl mx-auto mb-4">
                    {{ substr($teacher->user->name ?? 'T', 0, 1) }}
                </div>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ $teacher->user->name ?? 'Teacher' }}</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Employee ID: {{ $teacher->employee_id ?? 'N/A' }}</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $teacher->user->email ?? 'N/A' }}</p>
            </div>
        </x-ui.card>

        {{-- Details --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Professional Details</h3>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Qualification</p>
                            <p class="mt-1 text-neutral-900 dark:text-white">{{ $teacher->qualification ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Phone</p>
                            <p class="mt-1 text-neutral-900 dark:text-white">{{ $teacher->user->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Assignments</h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-2">Subjects</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($subjectAssignments->pluck('classSubject.subject')->unique() as $subject)
                                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">{{ $subject->name ?? 'N/A' }}</span>
                            @empty
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">No subjects assigned.</span>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-2">Classes</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($subjectAssignments->pluck('classSubject.class')->unique() as $class)
                                <span class="inline-flex items-center rounded-full bg-accent-100 dark:bg-accent-900/30 px-2.5 py-0.5 text-xs font-medium text-accent-700 dark:text-accent-300">{{ $class->name ?? 'N/A' }}</span>
                            @empty
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">No classes assigned.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
