<x-layouts.app title="Teacher Dashboard">
    {{-- Greeting --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $user->name ?? 'Teacher' }} 👋
        </h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
            @if($isClassTeacher)
                {{ $classAssignment->class->name ?? 'Class Teacher' }}
            @endif
            @if($isClassTeacher && $isSubjectTeacher)
                &middot;
            @endif
            @if($isSubjectTeacher)
                Subject Teacher
            @endif
            @if($isClassTeacher || $isSubjectTeacher)
                &middot;
            @endif
            {{ $currentTerm?->academicSession?->name ?? 'Current Session' }} &middot; {{ $currentTerm?->name ?? 'Current Term' }}
        </p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <x-ui.stat-card compact label="Classes Taught" :value="$subjectAssignments->unique('classSubject.class_id')->count()" icon="graduation-cap" />
        <x-ui.stat-card compact label="Subjects Taught" :value="$subjectAssignments->pluck('classSubject.subject_id')->unique()->count()" icon="book-open" />
        <x-ui.stat-card compact label="Students Taught" :value="$totalStudents" icon="users" />
        <x-ui.stat-card compact label="Attendance Today" :value="$attendanceKpiValue ?: 'N/A'" icon="calendar-check" />
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left Column: Today's Classes (4/12) --}}
        <div class="lg:col-span-4 h-full flex flex-col">
            <x-ui.card class="h-full flex flex-col">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Today's Classes</h3>
                    <a href="{{ route('teacher.timetable') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                </div>
                <div class="p-4 flex-1 overflow-y-auto">
                    @forelse($todayClasses as $period)
                        <div class="flex items-start gap-3 p-3 rounded-lg border border-neutral-100 dark:border-neutral-800 hover:border-primary-200 dark:hover:border-primary-800 transition-colors">
                            <div class="flex-shrink-0 w-16 text-center">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($period->start_time)->format('g:i A') }}
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    – {{ \Carbon\Carbon::parse($period->end_time)->format('g:i A') }}
                                </p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $period->classSubject->subject->name ?? 'N/A' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $period->classSubject->class->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">No classes scheduled for today.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- Right Column: My Classes + Announcements (8/12) --}}
        <div class="lg:col-span-8 h-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 h-full">
                {{-- My Classes --}}
                <div class="h-full flex flex-col">
                    <x-ui.card class="h-full flex flex-col">
                        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Classes</h3>
                            <a href="{{ route('teacher.classes.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                        </div>
                        <div class="p-4 flex-1 flex flex-col gap-3 overflow-y-auto">
                            @forelse($subjectAssignments->unique('classSubject.class_id') as $assignment)
                                @php $cls = $assignment->classSubject->class @endphp
                                <a href="{{ route('teacher.classes.show', $cls) }}" class="flex items-center justify-between p-3 rounded-lg border border-neutral-100 dark:border-neutral-800 hover:border-primary-200 dark:hover:border-primary-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $cls->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $cls->students->count() ?? 0 }} students</p>
                                    </div>
                                    <svg class="h-4 w-4 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @empty
                                <div class="text-center py-8">
                                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7" /></svg>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No classes assigned yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </x-ui.card>
                </div>

                {{-- Announcements --}}
                <div class="h-full flex flex-col">
                    <x-ui.card class="h-full flex flex-col">
                        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h3>
                            <a href="{{ route('teacher.announcements') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                        </div>
                        <div class="p-4 flex-1 space-y-3 overflow-y-auto">
                            @forelse($recentAnnouncements as $announcement)
                                <a href="{{ route('teacher.announcements') }}" class="block border-b border-neutral-200 dark:border-neutral-800 last:border-0 pb-3 last:pb-0 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 -mx-2 px-2 rounded transition-colors">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $announcement->title }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 100) }}</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">{{ $announcement->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No announcements.</p>
                            @endforelse
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions (Admin Dashboard Style) --}}
    <div class="mb-6 mt-8">
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Quick Actions</h3>
            </div>
            <div class="p-6 grid grid-cols-1 gap-3">
                <a href="{{ route('teacher.attendance') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                    <div class="h-8 w-8 rounded-lg bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Mark Attendance</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Record class attendance</p>
                    </div>
                </a>
                <a href="{{ route('teacher.scores') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                    <div class="h-8 w-8 rounded-lg bg-accent-200 dark:bg-accent-900/30 text-accent-700 dark:text-accent-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Enter Results</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Record student scores</p>
                    </div>
                </a>
                <a href="{{ route('teacher.report-cards.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                    <div class="h-8 w-8 rounded-lg bg-purple-200 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Report Cards</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Generate & publish</p>
                    </div>
                </a>
                <a href="{{ route('teacher.timetable') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                    <div class="h-8 w-8 rounded-lg bg-amber-200 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Timetable</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">View weekly schedule</p>
                    </div>
                </a>
                <a href="{{ route('teacher.classes.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-400 dark:hover:border-primary-700 hover:bg-primary-200 dark:hover:bg-primary-900/10 transition-colors">
                    <div class="h-8 w-8 rounded-lg bg-success-200 dark:bg-success-900/30 text-success-700 dark:text-success-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">My Classes</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Manage class details</p>
                    </div>
                </a>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>