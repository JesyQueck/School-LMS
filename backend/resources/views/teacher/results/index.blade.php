<x-layouts.app title="Results">
    <x-ui.breadcrumbs>
        <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
        <x-ui.breadcrumb-item active>Results</x-ui.breadcrumb-item>
    </x-ui.breadcrumbs>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Results</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Enter and manage results for your assigned subjects.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Subject Selection --}}
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Subjects</h3>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($assignments as $assignment)
                        <a href="{{ route('teacher.scores') }}?class_subject_id={{ $assignment->class_subject_id }}"
                           class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors block">
                            <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">
                                {{ substr($assignment->classSubject->subject->name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->classSubject->subject->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $assignment->classSubject->class->name ?? 'Unknown' }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No subject assignments found.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- Results Entry Area --}}
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Score Entry</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Select a subject from the left to enter scores.</p>
                </div>
                <div class="p-6">
                    @if(request('class_subject_id'))
                        @php
                            $selectedAssignment = $assignments->firstWhere('class_subject_id', request('class_subject_id'));
                            $selectedClassSubject = $selectedAssignment->classSubject ?? null;
                            $students = $selectedClassSubject ? \App\Models\Student::where('class_id', $selectedClassSubject->class_id)->get() : collect();
                            $term = \App\Models\Term::where('is_current', true)->first();
                        @endphp
                        @if($selectedClassSubject && $students->isNotEmpty())
                            <form method="POST" action="{{ route('teacher.scores.store') }}">
                                @csrf
                                <input type="hidden" name="class_subject_id" value="{{ $selectedClassSubject->id }}">
                                <input type="hidden" name="term_id" value="{{ $term?->id }}">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">CA Score</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Exam Score</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                            @foreach($students as $student)
                                                <tr>
                                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4">
                                                        <input type="number" name="scores[{{ $student->id }}][ca_score]" min="0" max="40" class="w-24 rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm">
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <input type="number" name="scores[{{ $student->id }}][exam_score]" min="0" max="60" class="w-24 rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2 rounded-lg text-sm">Save Scores</button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No students found for this subject.</p>
                        @endif
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-8">Select a subject from the list to start entering scores.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
