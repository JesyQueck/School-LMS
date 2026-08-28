<x-layouts.app title="Results">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Results</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Results</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Select a subject to enter and manage scores.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        @forelse($assignments as $assignment)
            <a href="{{ route('teacher.scores', ['class_subject_id' => $assignment->class_subject_id]) }}"
               class="block">
                <x-ui.card hoverable :padding="false">
                    <div class="p-6 text-center">
                        <div class="h-12 w-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-lg font-medium mx-auto mb-3">
                            {{ substr($assignment->classSubject->subject->name ?? 'S', 0, 1) }}
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1">{{ $assignment->classSubject->subject->name ?? 'Unknown Subject' }}</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $assignment->classSubject->class->name ?? 'Unknown Class' }}</p>
                        <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">CA/{{ $assignment->classSubject->ca_max ?? 30 }} + Exam/{{ $assignment->classSubject->exam_max ?? 70 }}</p>
                    </div>
                </x-ui.card>
            </a>
        @empty
            <div class="lg:col-span-4">
                <x-ui.card>
                    <div class="p-6 text-center">
                        <p class="text-neutral-500 dark:text-neutral-400 mb-4">No subject assignments found.</p>
                        <p class="text-sm text-neutral-400 dark:text-neutral-500">Please contact your administrator to be assigned subjects.</p>
                    </div>
                </x-ui.card>
            </div>
        @endforelse
    </div>
</x-layouts.app>
