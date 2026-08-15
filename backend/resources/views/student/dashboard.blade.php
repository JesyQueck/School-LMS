<x-layouts.app title="Student Dashboard">
    @php
        $user = auth()->user();
        $firstName = $student->first_name ?? ($student->full_name ?? 'Student');
        $greeting = 'Good ' . (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening'));
        $session = $currentTerm && $currentTerm->academicSession ? $currentTerm->academicSession->name : 'Current Session';
        $termName = $currentTerm ? $currentTerm->name : 'Current Term';
        $classStatus = $totalExpected > 0
            ? ($outstanding <= 0 ? 'Paid' : ($totalPaid > 0 ? 'Partially Paid' : 'Unpaid'))
            : 'No Fees';
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $greeting }}, {{ $firstName }} 👋</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Welcome back to Greenfield Academy.</p>
        <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-2">
            {{ $student->schoolClass->name ?? 'N/A' }} &middot; {{ $session }} &middot; {{ $termName }}
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Class" :value="$student->schoolClass->name ?? 'N/A'" icon="school" />
        <x-ui.stat-card label="Average Score" :value="$averageScore . '%'" icon="clipboard-list" />
        <x-ui.stat-card label="Attendance" :value="$attendanceRate . '%'" icon="calendar-check" />
        <x-ui.stat-card label="Subjects" :value="$subjectCount" icon="book-open" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Today's Classes --}}
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Today's Classes</h3>
            </div>
            <div class="p-6 space-y-3">
                @forelse($todayClasses as $class)
                    <div class="flex items-center gap-3 p-2 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                        <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-medium text-xs flex-shrink-0">
                            {{ $class['period'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $class['subject'] }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $class['teacher'] }}</p>
                        </div>
                        <span class="text-xs text-neutral-400 dark:text-neutral-500 flex-shrink-0">{{ $class['time'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No classes scheduled for today.</p>
                @endforelse
            </div>
            <div class="px-6 pb-6">
                <a href="{{ route('student.timetable') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Timetable</a>
            </div>
        </x-ui.card>

        {{-- My Attendance --}}
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">My Attendance</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-3 gap-3 mb-4 text-center">
                    <div>
                        <p class="text-xl font-bold text-success-600 dark:text-success-400">{{ $present }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Present</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-danger-600 dark:text-danger-400">{{ $absent }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Absent</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-warning-600 dark:text-warning-400">{{ $late }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Late</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-500 dark:text-neutral-400">Attendance Rate</span>
                    <span class="text-lg font-bold text-neutral-900 dark:text-white">{{ $attendanceRate }}%</span>
                </div>
                <div class="mt-4">
                    <a href="{{ route('student.attendance') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Attendance</a>
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        {{-- My Performance --}}
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Performance</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Your published results.</p>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($student->results->whereIn('term_id', $publishedTermIds) as $result)
                        @php $pct = $result->total ?? 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-700 dark:text-neutral-300">{{ $result->classSubject->subject->name ?? 'N/A' }}</span>
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ $pct }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                                <div class="h-full rounded-full bg-primary-500" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No published results yet.</p>
                    @endforelse

                    @if($subjectCount > 0)
                        <div class="flex items-center justify-between pt-3 border-t border-neutral-100 dark:border-dark-border">
                            <span class="text-sm font-semibold text-neutral-900 dark:text-white">Overall Average</span>
                            <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ $averageScore }}%</span>
                        </div>
                    @endif
                </div>
                @if($subjectCount > 0)
                    <div class="px-6 pb-6">
                        <a href="{{ route('student.report-cards') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Full Results</a>
                    </div>
                @endif
            </x-ui.card>
        </div>

        {{-- Latest Report Card --}}
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Latest Report Card</h3>
                </div>
                <div class="p-6">
                    @if($latestReportCard)
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $latestReportCard->term->name ?? 'Term' }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $latestReportCard->term->academicSession->name ?? '' }}</p>
                        <div class="mt-3 space-y-1 text-sm">
                            <p class="text-neutral-700 dark:text-neutral-300">Average: <span class="font-semibold text-neutral-900 dark:text-white">{{ $averageScore }}%</span></p>
                            <p class="text-neutral-700 dark:text-neutral-300">Grade: <span class="font-semibold text-neutral-900 dark:text-white">{{ $grade ?? 'N/A' }}</span></p>
                        </div>
                        <p class="mt-3 inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Published</p>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('student.report-cards') }}" class="flex-1 text-center text-xs font-medium px-3 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">View</a>
                            <a href="{{ route('student.report-cards.download', $latestReportCard) }}" class="flex-1 text-center text-xs font-medium px-3 py-2 rounded-lg border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Download PDF</a>
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No published report card yet.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        {{-- School Fees (secondary) --}}
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">School Fees</h3>
                </div>
                <div class="p-6 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Total</span>
                        <span class="font-medium text-neutral-900 dark:text-white">₦{{ number_format($totalExpected, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Paid</span>
                        <span class="font-medium text-success-600 dark:text-success-400">₦{{ number_format($totalPaid, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Outstanding</span>
                        <span class="font-medium {{ $outstanding > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">₦{{ number_format($outstanding, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-neutral-100 dark:border-dark-border">
                        <span class="text-neutral-500 dark:text-neutral-400">Status</span>
                        <span class="font-semibold text-neutral-900 dark:text-white">{{ $classStatus }}</span>
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <a href="{{ route('student.fees') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Fee Details</a>
                </div>
            </x-ui.card>
        </div>

        {{-- My Profile (compact) --}}
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Profile</h3>
                </div>
                <div class="p-6 space-y-1 text-sm">
                    <p class="text-neutral-700 dark:text-neutral-300">{{ $student->full_name ?? '---' }}</p>
                    <p class="text-neutral-500 dark:text-neutral-400">Admission No: {{ $student->admission_no ?? 'N/A' }}</p>
                    <p class="text-neutral-500 dark:text-neutral-400">Class: {{ $student->schoolClass->name ?? 'N/A' }}</p>
                    <p class="text-neutral-500 dark:text-neutral-400">Gender: {{ ucfirst($student->gender ?? 'N/A') }}</p>
                    <p class="text-neutral-500 dark:text-neutral-400">Date of Birth: {{ $student->date_of_birth ? $student->date_of_birth->format('j F Y') : 'N/A' }}</p>
                </div>
                <div class="px-6 pb-6">
                    <a href="{{ route('student.profile') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View Profile</a>
                </div>
            </x-ui.card>
        </div>

        {{-- Announcements --}}
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Announcements</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($announcements as $announcement)
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">📢 {{ $announcement->title }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ $announcement->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No announcements.</p>
                    @endforelse
                </div>
                <div class="px-6 pb-6">
                    <a href="{{ route('student.announcements') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All Announcements</a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
