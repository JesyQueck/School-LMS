<x-layouts.app :title="'Enter Scores'">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('teacher.dashboard') }}">Teacher</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('teacher.results') }}">Results</x-ui.breadcrumb-item>
            @if($selectedAssignment)
                <x-ui.breadcrumb-item active>{{ $selectedAssignment->classSubject->subject->name ?? 'Scores' }}</x-ui.breadcrumb-item>
            @else
                <x-ui.breadcrumb-item active>Enter Scores</x-ui.breadcrumb-item>
            @endif
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">
            @if($selectedAssignment)
                {{ $selectedAssignment->classSubject->subject->name ?? 'Scores' }} - {{ $selectedAssignment->classSubject->class->name ?? '' }}
            @else
                Enter Scores
            @endif
        </h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            @if($selectedAssignment)
                Enter CA and Examination scores for {{ $selectedAssignment->classSubject->subject->name ?? 'the selected subject' }}
            @else
                Select a subject from the left to enter scores.
            @endif
        </p>
    </div>

    @if($selectedAssignment && $students->isNotEmpty())
        <form id="scores-form" method="POST" action="{{ route('teacher.scores.store') }}">
            @csrf
            <input type="hidden" name="class_subject_id" value="{{ $selectedAssignment->class_subject_id }}">
            <input type="hidden" name="term_id" value="{{ $classAssignment->term_id }}">
            <input type="hidden" name="class_id" value="{{ $classAssignment->class_id }}">

            <x-ui.card>
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $selectedAssignment->classSubject->subject->name ?? 'Unknown' }}</h3>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-neutral-50 dark:bg-neutral-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Student</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">CA /{{ $caMax }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Exam /{{ $examMax }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Total /{{ $caMax + $examMax }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                @php
                                    $result = $student->results
                                        ->where('term_id', $classAssignment->term_id)
                                        ->where('class_subject_id', $selectedAssignment->class_subject_id)
                                        ->first();
                                @endphp
                                <tr class="border-b border-neutral-100 dark:border-neutral-800 last:border-0">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-neutral-900 dark:text-white">{{ $student->name }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $student->admission_no }}</div>
                                    </td>
                                     <td class="px-4 py-3 text-center">
                                         <input type="number"
                                                min="0"
                                                max="{{ $caMax }}"
                                                step="0.5"
                                                name="results[{{ $selectedAssignment->class_subject_id }}][{{ $student->id }}][ca_score]"
                                                class="w-16 px-2 py-1 border border-neutral-300 dark:border-dark-border rounded-lg text-sm text-center bg-neutral-50 dark:bg-neutral-800"
                                                value="{{ $result?->ca_score ?? '' }}">
                                     </td>
                                     <td class="px-4 py-3 text-center">
                                         <input type="number"
                                                min="0"
                                                max="{{ $examMax }}"
                                                step="0.5"
                                                name="results[{{ $selectedAssignment->class_subject_id }}][{{ $student->id }}][exam_score]"
                                                class="w-16 px-2 py-1 border border-neutral-300 dark:border-dark-border rounded-lg text-sm text-center bg-neutral-50 dark:bg-neutral-800"
                                                value="{{ $result?->exam_score ?? '' }}">
                                     </td>
                                    @php
                                        $ca = $result?->ca_score ?? 0;
                                        $exam = $result?->exam_score ?? 0;
                                        $total = ($ca ?: 0) + ($exam ?: 0);
                                        $grading = app(\App\Services\ResultService::class)->calculateGrade($total > 0 ? $total : null);
                                        $grade = $grading['grade'];
                                    @endphp
                                    <td class="px-4 py-3 text-center font-medium text-neutral-900 dark:text-white">{{ $total > 0 ? $total : '' }}</td>
                                    <td class="font-bold text-center" style="color: {{ in_array($grade, ['F9', 'D7', 'E8']) ? '#A3312B' : '#16324F' }};">{{ $grade ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-ui.card>

            <div class="px-4 py-3 border-t border-neutral-200 dark:border-dark-border flex gap-3 justify-end">
                <a href="{{ route('teacher.results') }}" class="px-6 py-2 text-sm text-neutral-700 dark:text-neutral-300 rounded-lg border border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors inline-flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m0 14l7-7-7-7" /></svg>
                    Back to Subjects
                </a>
                <button type="submit" class="px-6 py-2 text-sm text-white rounded-lg bg-primary-600 hover:bg-primary-700 transition-colors inline-flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    Save Scores
                </button>
            </div>
        </form>
    @elseif($assignments->isEmpty())
        <x-ui.card>
            <div class="p-6 text-center">
                <p class="text-neutral-500 dark:text-neutral-400 mb-4">No subjects assigned to you.</p>
                <p class="text-sm text-neutral-400 dark:text-neutral-500">Please contact your administrator to be assigned subjects.</p>
            </div>
        </x-ui.card>
    @elseif($selectedAssignment && $students->isEmpty())
        <x-ui.card>
            <div class="p-6 text-center">
                <p class="text-neutral-500 dark:text-neutral-400 mb-4">No students found for this subject.</p>
            </div>
        </x-ui.card>
    @else
        <x-ui.card>
            <div class="p-6 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No students found in your class for the current term.</p>
            </div>
        </x-ui.card>
    @endif
</x-layouts.app>
