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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Classes" :value="$subjectAssignments->unique('classSubject.class_id')->count()" icon="school" />
        <x-ui.stat-card label="Subjects" :value="$subjectAssignments->pluck('classSubject.subject_id')->unique()->count()" icon="book-open" />
        <x-ui.stat-card label="Students" :value="$totalStudents" icon="users" />
        <x-ui.stat-card label="Attendance Today" :value="$todayAttendanceRate . '%'" icon="calendar-check" />
    </div>

    {{-- Today's Classes + Announcements --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        {{-- Today's Classes --}}
        <div class="lg:col-span-5">
            <x-ui.card>
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Today's Classes</h3>
                </div>
                <div class="p-4">
                    @forelse($todayClasses as $period)
                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 mb-2">
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
                <div class="px-4 pb-4">
                    <a href="{{ route('teacher.timetable') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Timetable</a>
                </div>
            </x-ui.card>
        </div>

        {{-- Announcements --}}
        <div class="lg:col-span-7">
            <x-ui.card>
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border flex items-center gap-2">
                    <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h3>
                </div>
                <div class="p-4">
                    @forelse($recentAnnouncements as $announcement)
                        <div class="mb-3 last:mb-0">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $announcement->title }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 100) }}</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">{{ $announcement->created_at->format('M d, Y') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No announcements.</p>
                    @endforelse
                </div>
                <div class="px-4 pb-4">
                    <a href="{{ route('teacher.announcements') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- My Classes --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-4">My Classes</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($myClasses as $assignment)
                @php
                    $cls = $assignment->classSubject->class;
                    $isFormTeacher = $classAssignment && $classAssignment->class_id === $cls->id;
                    $subjectsForClass = $subjectAssignments->filter(fn ($a) => $a->classSubject->class_id === $cls->id)->pluck('classSubject.subject.name')->unique();
                @endphp
                <x-ui.card :padding="false">
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ $cls->name ?? 'N/A' }}</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ $cls->students->count() ?? 0 }} students</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            {{ $isFormTeacher ? 'Class Teacher' : 'Subject Teacher' }}
                            @if($subjectsForClass->isNotEmpty())
                                &middot; {{ $subjectsForClass->first() }}
                            @endif
                        </p>
                        <a href="{{ route('teacher.classes.show', $cls) }}" class="inline-block mt-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">Open</a>
                    </div>
                </x-ui.card>
            @empty
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No classes assigned yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('teacher.class.attendance') }}" class="block">
                <x-ui.card hoverable :padding="false">
                    <div class="p-4 text-center">
                        <svg class="h-8 w-8 text-primary-600 dark:text-primary-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-1.82-2.9l-3.64-1.27A6 6 0 0015 6.5V4a3 3 0 10-6 0v2.5a6 6 0 00-1.54 1.27l-3.64 1.27A3 3 0 003 14v2m14 0v5a3 3 0 01-3 3h-2M9 10h.01M15 10h.01" />
                        </svg>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Mark Attendance</p>
                    </div>
                </x-ui.card>
            </a>
            <a href="{{ route('teacher.scores') }}" class="block">
                <x-ui.card hoverable :padding="false">
                    <div class="p-4 text-center">
                        <svg class="h-8 w-8 text-primary-600 dark:text-primary-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a2 2 0 11-4 0 2 2 0 114 0zm-6 0a2 2 0 11-4 0 2 2 0 114 0z" />
                        </svg>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Enter Results</p>
                    </div>
                </x-ui.card>
            </a>
            <a href="{{ route('teacher.report-cards.index') }}" class="block">
                <x-ui.card hoverable :padding="false">
                    <div class="p-4 text-center">
                        <svg class="h-8 w-8 text-primary-600 dark:text-primary-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a2 2 0 012 2v5a2 2 0 01-2 2H7a2 2 0 01-2-2v-5a2 2 0 012-2h4" />
                        </svg>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Report Cards</p>
                    </div>
                </x-ui.card>
            </a>
        </div>
    </div>
</x-layouts.app>
