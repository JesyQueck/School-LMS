<x-layouts.app title="Teacher Dashboard">
    @php
        $user = auth()->user();
        $teacher = $user->teacher;
        $fullName = $user->name ?? 'Teacher';
        $employeeId = $teacher->employee_id ?? 'N/A';
        $currentTerm = \App\Models\Term::where('is_current', true)->with('academicSession')->first();
        $sessionName = $currentTerm && $currentTerm->academicSession ? $currentTerm->academicSession->name : 'Current Session';
        $termName = $currentTerm ? $currentTerm->name : 'Current Term';

        $isClassTeacher = $teacher ? \App\Models\ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->exists() : false;

        $isSubjectTeacher = $teacher ? \App\Models\TeacherClassSubject::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->exists() : false;

        $subjectAssignments = $teacher
            ? \App\Models\TeacherClassSubject::with(['classSubject.class', 'classSubject.subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get()
            : collect();

        $classAssignment = $isClassTeacher
            ? \App\Models\ClassAssignment::with(['class', 'term', 'academicSession'])
                ->where('teacher_id', $teacher->id)
                ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
                ->first()
            : null;

        $myClassIds = $subjectAssignments->pluck('classSubject.class_id')->unique();
        $totalStudents = \App\Models\Student::whereIn('class_id', $myClassIds)->count();

        $todayClasses = \App\Models\Timetable::with(['classSubject.subject', 'classSubject.class'])
            ->whereHas('classSubject', fn ($q) => $q->whereIn('id', $subjectAssignments->pluck('class_subject_id')))
            ->where('day_of_week', now()->format('l'))
            ->orderBy('start_time')
            ->get();

        $todayAttendanceRate = $totalStudents > 0 ? round(($totalStudents / max($totalStudents, 1)) * 100) : 0;

        $recentAnnouncements = \App\Models\Announcement::where('target_audience', 'all')
            ->orWhere('target_audience', 'teacher')
            ->latest()
            ->limit(3)
            ->get();
    @endphp

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $fullName }} 👋</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Welcome back to Greenfield Academy.</p>
        <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-2">
            @if($isClassTeacher && $isSubjectTeacher)
                Class Teacher — {{ $classAssignment->class->name ?? 'N/A' }} &middot; Subject Teacher &middot; {{ $sessionName }} &middot; {{ $termName }}
            @elseif($isClassTeacher)
                Class Teacher — {{ $classAssignment->class->name ?? 'N/A' }} &middot; {{ $sessionName }} &middot; {{ $termName }}
            @elseif($isSubjectTeacher)
                Subject Teacher &middot; {{ $sessionName }} &middot; {{ $termName }}
            @else
                {{ $sessionName }} &middot; {{ $termName }}
            @endif
        </p>
        <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Employee ID: {{ $employeeId }}</p>
    </div>

    {{-- Quick Statistics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="My Classes" :value="$subjectAssignments->count()" icon="school" />
        <x-ui.stat-card label="My Subjects" :value="$subjectAssignments->pluck('classSubject.subject_id')->unique()->count()" icon="book-open" />
        <x-ui.stat-card label="Students" :value="$totalStudents" icon="users" />
        <x-ui.stat-card label="Attendance Today" :value="$todayAttendanceRate . '%'" icon="calendar-check" />
        @if($isClassTeacher && $classAssignment)
            <x-ui.stat-card
                label="My Class"
                :value="$classAssignment->class->name ?? 'N/A'"
                :trend="['direction' => 'neutral', 'value' => ($classAssignment->class->students->count() ?? 0) . ' students']"
                icon="users"
            />
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        {{-- Today's Timetable --}}
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Today's Classes</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Your teaching schedule for today.</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($todayClasses as $period)
                            <div class="flex items-center gap-4 p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                                <div class="flex-shrink-0 text-center">
                                    <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500">{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</p>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $period->classSubject->subject->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $period->classSubject->class->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No classes scheduled for today.</p>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('teacher.timetable') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Timetable</a>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Right Column --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- My Classes --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Classes</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($subjectAssignments->unique('classSubject.class_id') as $assignment)
                        @php $cls = $assignment->classSubject->class @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 dark:border-dark-border">
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $cls->name ?? 'N/A' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $cls->students->count() ?? 0 }} students</p>
                            </div>
                            <a href="{{ route('teacher.classes.show', $cls) }}" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">Open</a>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No classes assigned yet.</p>
                    @endforelse
                </div>
            </x-ui.card>

            {{-- Announcements --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($recentAnnouncements as $announcement)
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">📢 {{ $announcement->title }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ $announcement->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No announcements.</p>
                    @endforelse
                </div>
                <div class="px-6 pb-6">
                    <a href="{{ route('teacher.announcements') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All Announcements</a>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Class Teacher Section --}}
    @if($isClassTeacher && $classAssignment)
        @php
            $formClass = $classAssignment->class;
            $formStudents = \App\Models\Student::where('class_id', $formClass->id)->get();
            $formClassAvg = $formStudents->isNotEmpty() ? round($formStudents->avg(function ($s) { return $s->results->avg('total') ?? 0; }), 1) : 0;
            $formAttendanceRecords = \App\Models\Attendance::where('class_id', $formClass->id)
                ->where('term_id', $classAssignment->term_id)
                ->whereDate('created_at', '>=', now()->startOfMonth())
                ->get();
            $presentCount = $formAttendanceRecords->where('status', 'present')->count();
            $totalAttendanceRecords = $formAttendanceRecords->count();
            $formAttendanceRate = $totalAttendanceRecords > 0 ? round(($presentCount / $totalAttendanceRecords) * 100) : 0;
        @endphp
        <div class="mb-6">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Form Class</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $formClass->name }} — Class Teacher Overview</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="text-center p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800">
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $formStudents->count() }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Students</p>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800">
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $formClassAvg }}%</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Class Average</p>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800">
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $formAttendanceRate }}%</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Attendance Rate</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('teacher.class.students', $formClass) }}" class="flex-1 text-center text-sm font-medium px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">View Students</a>
                        <a href="{{ route('teacher.class.attendance') }}" class="flex-1 text-center text-sm font-medium px-4 py-2 rounded-lg border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Attendance</a>
                        <a href="{{ route('teacher.class-performance') }}" class="flex-1 text-center text-sm font-medium px-4 py-2 rounded-lg border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Class Performance</a>
                    </div>
                </div>
            </x-ui.card>
        </div>
    @endif
</x-layouts.app>
