<x-layouts.app title="Attendance">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Attendance</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">Mark attendance for your class</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    @if($classAssignment)

        @php
            $today = now()->toDateString();
        @endphp

        {{-- Overview: attendance was saved for today --}}
        @if($showOverview)
            @php
                $presentStudents = $todayAttendances->where('status', 'present');
                $absentStudents = $todayAttendances->where('status', 'absent');
                $totalStudents = $students->count();
                $presentCount = $presentStudents->count();
                $absentCount = $absentStudents->count();
            @endphp
            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">
                        Class: {{ $classAssignment->class->name ?? 'N/A' }}
                        @if($classAssignment->term)
                            ({{ $classAssignment->term->name }})
                        @endif
                    </h3>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Date: {{ $today }}</span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-center">
                        <div class="bg-success-50 dark:bg-success-900/20 rounded-lg p-4">
                            <div class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $presentCount }}</div>
                            <div class="text-xs text-neutral-600 dark:text-neutral-400">Present</div>
                        </div>
                        <div class="bg-danger-50 dark:bg-danger-900/20 rounded-lg p-4">
                            <div class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $absentCount }}</div>
                            <div class="text-xs text-neutral-600 dark:text-neutral-400">Absent</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Present --}}
                        <div class="border border-neutral-200 dark:border-dark-border rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-success-700 dark:text-success-300 mb-2">Present ({{ $presentCount }}/{{ $totalStudents }})</h4>
                            <ul class="space-y-1 text-sm">
                                @forelse($presentStudents as $att)
                                    <li class="text-neutral-700 dark:text-neutral-300">{{ $att->student->full_name ?? 'N/A' }}</li>
                                @empty
                                    <li class="text-neutral-400">No present students.</li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- Absent --}}
                        <div class="border border-neutral-200 dark:border-dark-border rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-danger-700 dark:text-danger-300 mb-2">Absent ({{ $absentCount }})</h4>
                            <ul class="space-y-1 text-sm">
                                @forelse($absentStudents as $att)
                                    <li class="text-neutral-700 dark:text-neutral-300">{{ $att->student->full_name ?? 'N/A' }} <span class="text-xs text-danger-500">(Absent)</span></li>
                                @empty
                                    <li class="text-neutral-400">No absent students.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('teacher.attendance.edit') }}" class="px-4 py-2 border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text bg-white dark:bg-dark-surface hover:bg-neutral-50 dark:hover:bg-neutral-800 rounded-lg font-medium transition-colors text-sm">
                            Edit Attendance
                        </a>
                    </div>
                </div>
            </x-ui.card>

        {{-- Marking form (Take Attendance was clicked) --}}
        @elseif($showAttendanceForm)
            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">
                        Class: {{ $classAssignment->class->name ?? 'N/A' }}
                        @if($classAssignment->term)
                            ({{ $classAssignment->term->name }})
                        @endif
                    </h3>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Date: {{ $today }}</span>
                </div>

                <form id="attendance-form" method="POST" action="{{ route('teacher.attendance.store') }}">
                    @csrf
                    <div class="px-6 pb-5">
                        <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">
                        <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">

                        <table class="w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                            <thead class="bg-neutral-50 dark:bg-neutral-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Student</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Present</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Absent</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-200 dark:divide-neutral-800">
                                @foreach($students as $student)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                                        {{ $student->full_name ?? $student->name ?? $student->admission_no }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input type="hidden" name="date" value="{{ $today }}">
                                        <input type="radio" name="status[{{ $student->id }}]" value="present" class="h-4 w-4 text-primary-600 border-neutral-300 focus:ring-primary-500" checked>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input type="radio" name="status[{{ $student->id }}]" value="absent" class="h-4 w-4 text-danger-600 border-neutral-300 focus:ring-danger-500">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-success-600 hover:bg-success-700 text-white rounded-lg font-medium transition-colors">
                                Save Attendance
                            </button>
                        </div>
                    </div>
                </form>
            </x-ui.card>

        {{-- Initial prompt (resets on a new day) --}}
        @else
            <x-ui.card>
                <div class="p-6 text-center">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">Take Attendance for Today</h3>
                    <p class="text-neutral-500 dark:text-neutral-400 mb-4">Date: {{ $today }}</p>
                    <form method="POST" action="{{ route('teacher.attendance.start') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">
                        <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">
                        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                            Take Attendance
                        </button>
                    </form>
                </div>
            </x-ui.card>
        @endif

    @else
        <x-ui.card>
            <div class="p-6 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">No class assignment found for the current term.</p>
            </div>
        </x-ui.card>
    @endif
</x-layouts.app>
