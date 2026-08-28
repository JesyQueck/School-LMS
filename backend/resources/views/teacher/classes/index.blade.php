<x-layouts.app title="My Classes">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>My Classes</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Classes</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Classes assigned to you for teaching.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($assignments as $assignment)
            @php $cls = $assignment->classSubject->class @endphp
            <x-ui.card hoverable>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $cls->name ?? 'N/A' }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $assignment->classSubject->subject->name ?? 'N/A' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">
                            Active
                        </span>
                    </div>
                    <div class="space-y-2 text-sm text-neutral-600 dark:text-neutral-400">
                        <p>Students: {{ $cls->students->count() ?? 0 }}</p>
                        <p>Subject: {{ $assignment->classSubject->subject->name ?? 'N/A' }}</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('teacher.classes.show', $cls) }}" class="block text-center text-sm font-medium px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">Open Class</a>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-ui.empty-state title="No classes assigned" description="You have not been assigned to any classes yet. Contact the admin for assignments." />
            </div>
        @endforelse
    </div>
</x-layouts.app>
