<x-layouts.app title="Student Profile">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item href="{{ route('teacher.students.index') }}">My Students</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>{{ $student->full_name ?? 'Student' }}</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $student->full_name ?? 'N/A' }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            Admission No: {{ $student->admission_no ?? 'N/A' }} &middot; Class: {{ $student->schoolClass->name ?? 'N/A' }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <x-ui.card :padding="false">
                <div class="p-6 text-center">
                    @if($student->user && $student->user->profile_photo)
                        <img src="{{ asset('storage/'.$student->user->profile_photo) }}" alt="{{ $student->full_name }}" class="w-24 h-24 rounded-full mx-auto object-cover">
                    @else
                        <div class="w-24 h-24 rounded-full bg-neutral-200 dark:bg-neutral-700 mx-auto flex items-center justify-center">
                            <svg class="h-10 w-10 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 02-7 7h14a7 7 0 02-7-7z" />
                            </svg>
                        </div>
                    @endif
                    <h2 class="mt-4 text-xl font-bold text-neutral-900 dark:text-white">{{ $student->full_name ?? 'N/A' }}</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $student->schoolClass->name ?? 'Unassigned' }}</p>
                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">{{ $student->admission_no ?? 'N/A' }}</p>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-ui.card :padding="false">
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Personal Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Full Name</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Admission No</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->admission_no ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Class</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->schoolClass->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Gender</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->gender ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Date of Birth</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Blood Group</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->blood_group ?? '---' }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card :padding="false">
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Academic Summary</h3>
                </div>
                <div class="p-6">
                    @php
                        $results = $student->results ?? collect();
                        $passed = $results->where('total', '>=', 40)->count();
                        $failed = $results->where('total', '<', 40)->count();
                        $avg = $results->isNotEmpty() ? round($results->avg('total'), 1) : 0;
                    @endphp
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $results->count() }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Subjects Taken</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $passed }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Passed</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $avg }}%</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Average</p>
                        </div>
                    </div>

                    @if($results->isNotEmpty())
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Subject</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">CA</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Exam</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Total</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border">
                                    @foreach($results as $result)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $result->classSubject->subject->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $result->ca_score ?? 0 }}</td>
                                            <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $result->exam_score ?? 0 }}</td>
                                            <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $result->total ?? 0 }}</td>
                                            <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $result->grade ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-3">No results recorded yet.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('teacher.students.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">← Back to My Students</a>
    </div>
</x-layouts.app>
