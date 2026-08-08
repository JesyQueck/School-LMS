<x-layouts.app title="Assignments">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/student/dashboard">Student</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Assignments</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Assignments</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View and track your assignments.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.stat-card label="Total Assignments" :value="$assignments->count()" :trend="['direction' => 'neutral', 'value' => 'All assignments']" icon="book-open" />
        <x-ui.stat-card label="Overdue" :value="$overdue->count()" :trend="['direction' => 'up', 'value' => 'Need attention']" icon="alert-triangle" />
        <x-ui.stat-card label="Upcoming" :value="$upcoming->count()" :trend="['direction' => 'neutral', 'value' => 'Due this term']" icon="calendar" />
    </div>

    @if($assignments->count() > 0)
    <x-ui.card>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">All Assignments</h3>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @foreach($assignments as $assignment)
                <div class="border border-neutral-200 dark:border-dark-border rounded-lg p-4 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->title ?? 'Untitled Assignment' }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $assignment->subject->name ?? 'Unknown Subject' }} &middot; {{ ucfirst($assignment->type ?? 'assignment') }}</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1"> Due: {{ $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        @php
                            $status = $assignment->due_date && \Carbon\Carbon::parse($assignment->due_date)->isPast() ? 'overdue' : 'upcoming';
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full {{ $status === 'overdue' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' }}">
                            {{ $status === 'overdue' ? 'Overdue' : 'Upcoming' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </x-ui.card>
    @else
    <x-ui.card>
        <div class="p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H9z"/></svg>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">No assignments found.</p>
        </div>
    </x-ui.card>
    @endif

    <div class="mt-4">
        <a href="{{ route('student.dashboard') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
            ← Back to Dashboard
        </a>
    </div>
</x-layouts.app>