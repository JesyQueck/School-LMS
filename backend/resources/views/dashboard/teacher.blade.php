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
        @if($isClassTeacher)
            <x-ui.stat-card compact label="Form Class Attendance Today" :value="$formClassAttendanceRateValue" icon="calendar-check" />
        @else
            <x-ui.stat-card compact label="Attendance Today" :value="$todayAttendanceRate . '%'" icon="calendar-check" />
        @endif
    </div>

    {{-- Today's Classes + Announcements --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Today's Classes --}}
        <div class="lg:col-span-4 h-full flex flex-col">
            <x-ui.card class="h-full flex flex-col">
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border flex items-center gap-2">
                    <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002-2h2a2 2 0 002 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Today's Classes</h3>
                </div>
                <div class="p-4 flex-1 overflow-y-auto">
                    <div class="space-y-2">
                        @forelse($todayClasses as $period)
                            <div class="flex items-center gap-3 p-2.5 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                                <div class="flex-shrink-0 text-center">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($period->start_time)->format('g:i A') }}
                                    </p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        – {{ \Carbon\Carbon::parse($period->end_time)->format('g:i A') }}
                                    </p>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $period->classSubject->subject->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $period->classSubject->class->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-3">No classes scheduled for today.</p>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('teacher.timetable') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Timetable</a>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Right Column: My Classes + Announcements --}}
        <div class="lg:col-span-8 h-full">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 h-full">
                {{-- My Classes --}}
                <div class="h-full flex flex-col">
                    <x-ui.card class="h-full flex flex-col">
                        <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border flex items-center gap-2">
                            <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zM12 14v7" /></svg>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Classes</h3>
                        </div>
                        <div class="p-4 flex-1 flex flex-col gap-2 overflow-y-auto">
                            @forelse($subjectAssignments->unique('classSubject.class_id') as $assignment)
                                @php $cls = $assignment->classSubject->class @endphp
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-neutral-200 dark:border-dark-border">
                                    <div class="text-left">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $cls->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $cls->students->count() ?? 0 }} students</p>
                                    </div>
                                    <a href="{{ route('teacher.classes.show', $cls) }}" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">Open</a>
                                </div>
                            @empty
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-3">No classes assigned yet.</p>
                            @endforelse
                        </div>
                    </x-ui.card>
                </div>

                {{-- Announcements --}}
                <div class="h-full flex flex-col">
                    <x-ui.card class="h-full flex flex-col">
                        <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border flex items-center gap-2">
                            <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h3>
                        </div>
                        <div class="p-4 flex-1 flex flex-col gap-2 overflow-y-auto">
                            @forelse($recentAnnouncements as $announcement)
                                <div class="text-left">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $announcement->title }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 100) }}</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">{{ $announcement->created_at->format('M d, Y') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-3">No announcements.</p>
                            @endforelse
                        </div>
                        <div class="px-4 pb-4 mt-auto">
                            <a href="{{ route('teacher.announcements') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </div>

     {{-- Quick Actions --}}
    <div class="mb-6 mt-8">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('teacher.attendance') }}" class="block">
                <x-ui.card hoverable :padding="false">
                    <div class="p-4 text-center">
                        <svg class="h-8 w-8 text-primary-600 dark:text-primary-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Mark Attendance</p>
                    </div>
                </x-ui.card>
            </a>
            <a href="{{ route('teacher.scores') }}" class="block">
                <x-ui.card hoverable :padding="false">
                    <div class="p-4 text-center">
                        <svg class="h-8 w-8 text-primary-600 dark:text-primary-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Enter Results</p>
                    </div>
                </x-ui.card>
            </a>
            <a href="{{ route('teacher.report-cards.index') }}" class="block">
                <x-ui.card hoverable :padding="false">
                    <div class="p-4 text-center">
                        <svg class="h-8 w-8 text-primary-600 dark:text-primary-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Report Cards</p>
                    </div>
                </x-ui.card>
            </a>
        </div>
    </div>
</x-layouts.app>
