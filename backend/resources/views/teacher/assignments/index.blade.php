<x-layouts.app title="My Subjects">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">My Subjects</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">View your assigned subjects</p>
    </div>

    @if($assignments->count() > 0)
    <x-ui.card>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Assigned Subjects</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($assignments as $assignment)
                <div class="border border-neutral-200 dark:border-dark-border rounded-xl p-4 hover:shadow-sm transition-shadow">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->classSubject->subject->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                {{ $assignment->classSubject->class->name ?? 'Unknown' }}
                                @if($assignment->is_active)
                                    <span class="text-green-600 dark:text-green-400 text-xs">(Active)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </x-ui.card>
    @else
    <x-ui.card>
        <div class="p-6 text-center">
            <p class="text-neutral-500 dark:text-neutral-400 mb-4">No subjects assigned to you.</p>
            <p class="text-sm text-neutral-400 dark:text-neutral-500">Please contact your administrator to be assigned subjects.</p>
        </div>
    </x-ui.card>
    @endif
</x-layouts.app>