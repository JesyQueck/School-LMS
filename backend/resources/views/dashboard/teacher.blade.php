<x-layouts.app title="Teacher Dashboard">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">Here's your teaching overview at Greenfield Academy.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <x-ui.stat-card label="Total Students" :value="$assignments->count() * 30" :trend="['direction' => 'neutral', 'value' => 'across your classes']" icon="users" />
        <x-ui.stat-card label="Subject Assignments" :value="$assignments->where('is_active', true)->count()" :trend="['direction' => 'neutral', 'value' => 'active subjects']" icon="book-open" />
        <x-ui.stat-card label="Pending Grading" value="8" :trend="['direction' => 'down', 'value' => '3 due today']" icon="clipboard-list" />
        @if($classAssignment)
            <x-ui.stat-card label="My Class" :value="$classAssignment->class->name ?? 'N/A'" :trend="['direction' => 'neutral', 'value' => 'Class Teacher']" icon="school" />
        @else
            <x-ui.stat-card label="Attendance" value="96%" :trend="['direction' => 'up', 'value' => '1.2% from last week']" icon="calendar-check" />
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">
                        @if($classAssignment)
                            Class: {{ $classAssignment->class->name ?? 'My Class' }}
                        @else
                            Today's Schedule
                        @endif
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @if($classAssignment)
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                You are the Class Teacher for <strong>{{ $classAssignment->class->name ?? 'Unknown' }}</strong>
                                @if($classAssignment->term)
                                    ({{ $classAssignment->term->name }})
                                @endif.
                            </p>
                            @if($assignments->where('is_active', true)->count() > 0)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                    You also teach:
                                    <ul class="list-disc list-inside mt-1">
                                        @foreach($assignments->where('is_active', true) as $ta)
                                            <li class="text-sm">{{ $ta->classSubject->subject->name ?? 'Unknown' }} ({{ $ta->classSubject->class->name ?? 'Unknown' }})</li>
                                        @endforeach
                                    </ul>
                                </p>
                            @endif
                        @else
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                                    <div>
                                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">JSS 3-A</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Mathematics</p>
                                    </div>
                                    <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">8:00 - 9:00</span>
                                </div>
                                <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                                    <div>
                                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">JSS 2-B</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">English</p>
                                    </div>
                                    <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">9:00 - 10:00</span>
                                </div>
                                <div class="flex items-center justify-between py-3 border-b border-neutral-100 dark:border-neutral-800">
                                    <div>
                                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">JSS 1-C</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Physics</p>
                                    </div>
                                    <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">10:30 - 11:30</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-4">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Your Assignments</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if($assignments->where('is_active', true)->count() > 0)
                            @foreach($assignments->where('is_active', true) as $assignment)
                                <div class="flex items-start gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border">
                                    <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->classSubject->subject->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $assignment->classSubject->class->name ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No active subject assignments found.</p>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>