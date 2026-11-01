<x-layouts.app title="Report Cards">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Report Cards</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Review student results grouped by class and fill in principal details.</p>
        </div>
        <form method="GET" action="{{ route('admin.report-cards.index') }}" class="flex items-center gap-2">
            <select name="term_id" onchange="this.form.submit()" class="rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
                <option value="">{{ $currentTerm ? $currentTerm->name . ' (' . ($currentTerm->academicSession->name ?? '') . ')' : 'Select term' }}</option>
                @foreach($terms as $t)
                    <option value="{{ $t->id }}" {{ ($request->input('term_id', $currentTerm?->id) == $t->id) ? 'selected' : '' }}>{{ $t->name }} ({{ $t->academicSession->name ?? '' }})</option>
                @endforeach
            </select>
        </form>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <div class="flex flex-col gap-2">
                <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">Please fix the following errors:</h4>
                <ul class="list-disc list-inside text-sm text-danger-700 dark:text-danger-400 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </x-ui.alert>
    @endif

    @if(!$currentTerm && !$request->input('term_id'))
        <x-ui.alert variant="warning" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-.01-7a3 3 0 0 0-3 3v2a3 3 0 0 0 6 0V9a3 3 0 0 0-3-3Z"/></svg>
                <div class="flex-1 text-sm text-warning-800 dark:text-warning-300">No current term is selected. Please select a term above to view report cards.</div>
            </div>
        </x-ui.alert>
    @endif

    @forelse($classes as $class)
        @php
            $students = $class->students->sortBy('admission_no');
            $termId = $request->input('term_id') ?? ($currentTerm?->id);
            $submittedCount = $students->filter(fn($s) => $s->reportCards->firstWhere('term_id', $termId)?->isApproved() || $s->reportCards->firstWhere('term_id', $termId)?->isPublished())->count();
        @endphp
        <div class="card mb-6" x-data="{ expanded: false, show: false, iframeSrc: '' }">
            <div class="card-header flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $class->name }}</h3>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-dark-border px-2 py-0.5 rounded">
                            {{ $students->count() }} student(s) · {{ $submittedCount }} submitted
                        </span>
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        Form Teacher: {{ $class->formTeacher?->user?->name ?? 'Not assigned' }}
                    </p>
                </div>
                <div class="ml-4" @click.stop>
                    <button type="button"
                            onclick="saveClassReportCard({{ $class->id }})"
                            class="text-xs px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded transition-colors">
                        Save
                    </button>
                </div>
                <svg class="h-5 w-5 text-neutral-400 dark:text-neutral-500 transition-transform"
                     :class="expanded ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>

            <div x-show="expanded" x-collapse>
                <form id="class-form-{{ $class->id }}" method="POST" action="{{ route('admin.report-cards.save-class', $class->id) }}">
                    @csrf
                    <input type="hidden" name="term_id" value="{{ $termId }}">

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-neutral-50 dark:bg-neutral-800">
                                    <th class="px-3 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 w-8">#</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 w-40">Student</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300">Results</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 w-44">Principal Remark</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 w-32">Status</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-neutral-600 dark:text-neutral-300 w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach($students as $student)
                                    @php
                                        $studentResults = $student->results ?? collect();
                                        $reportCard = $student->reportCards->firstWhere('term_id', $termId) ?? $student->reportCards->first();
                                        $hasScores = $studentResults->whereNotNull('ca_score')->whereNotNull('exam_score')->isNotEmpty();
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-center text-sm text-neutral-600 dark:text-neutral-400">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-2">
                                            <div class="font-medium text-neutral-900 dark:text-white">{{ $student->full_name }}</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $student->admission_no }}</div>
                                        </td>
                                        <td class="px-3 py-2">
                                            @if($studentResults->isNotEmpty())
                                                <div class="space-y-0.5">
                                                    @foreach($studentResults as $result)
                                                        @php
                                                            $maxTotal = ($result->classSubject->ca_max ?? 30) + ($result->classSubject->exam_max ?? 70);
                                                            $gradeClass = '';
                                                            if ($result->total !== null) {
                                                                $pct = $maxTotal > 0 ? ($result->total / $maxTotal) * 100 : 0;
                                                                if ($pct >= 75) $gradeClass = 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200';
                                                                elseif ($pct >= 50) $gradeClass = 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200';
                                                                else $gradeClass = 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200';
                                                            }
                                                        @endphp
                                                        <div class="flex items-center gap-2 text-xs">
                                                            <span class="w-24 font-medium text-neutral-700 dark:text-neutral-300">{{ $result->classSubject->subject->name ?? 'N/A' }}</span>
                                                            <span class="w-7 text-right">{{ $result->ca_score ?? '-' }}</span>
                                                            <span class="text-neutral-400">/</span>
                                                            <span class="w-7 text-right">{{ $result->exam_score ?? '-' }}</span>
                                                            <span class="text-neutral-400">/</span>
                                                            <span class="w-10 text-right font-medium">{{ $result->total ?? '-' }}</span>
                                                            <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $gradeClass }}">{{ $result->grade ?? '-' }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400">No results yet</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            <textarea name="students[{{ $student->id }}][principal_remark]"
                                                      placeholder="Principal's final comments..."
                                                      class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-xs resize-y"
                                                      rows="2">{{ $reportCard->principal_remark ?? '' }}</textarea>
                                            <input type="hidden" name="students[{{ $student->id }}][student_id]" value="{{ $student->id }}">

                                            <div class="mt-2 space-y-1.5">
                                                <select name="students[{{ $student->id }}][promotion_decision]"
                                                        class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-xs">
                                                    <option value="">Promotion status</option>
                                                    <option value="promoted" {{ ($reportCard->promotion_decision ?? null) === 'promoted' ? 'selected' : '' }}>Promoted</option>
                                                    <option value="repeated" {{ ($reportCard->promotion_decision ?? null) === 'repeated' ? 'selected' : '' }}>Repeated</option>
                                                    <option value="transferred" {{ ($reportCard->promotion_decision ?? null) === 'transferred' ? 'selected' : '' }}>Transferred</option>
                                                </select>
                                                @if($reportCard)
                                                    @php
                                                        $position = $reportCard->calculatePositionInClass();
                                                        $totalStudents = $reportCard->getTotalStudentsInClass();
                                                    @endphp
                                                    <p class="text-xs text-neutral-600 dark:text-neutral-400">Pos: #{{ $position }} / {{ $totalStudents }}</p>
                                                    <input type="hidden" name="students[{{ $student->id }}][position_in_class]" value="{{ $position }}">
                                                    <input type="hidden" name="students[{{ $student->id }}][total_students_in_class]" value="{{ $totalStudents }}">
                                                @else
                                                    <input type="hidden" name="students[{{ $student->id }}][position_in_class]" value="">
                                                    <input type="hidden" name="students[{{ $student->id }}][total_students_in_class]" value="">
                                                @endif
                                                <input type="text" name="students[{{ $student->id }}][principal_signature]"
                                                       placeholder="Signature"
                                                       class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-xs"
                                                       value="{{ $reportCard->principal_signature ?? '' }}">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            @if($reportCard && $reportCard->isPublished())
                                                <span class="text-xs font-medium text-purple-600 dark:text-purple-400">Published</span>
                                            @elseif($reportCard && $reportCard->isApproved())
                                                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Approved</span>
                                            @elseif($reportCard)
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ ucfirst($reportCard->status) }}</span>
                                            @else
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400">No card</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col gap-1">
                                                @if($reportCard)
                                                    <button type="button"
                                                            @click="show = true; iframeSrc = '{{ route('admin.report-cards.render', $reportCard->id) }}'"
                                                            class="text-xs px-2 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded transition-colors">
                                                        View Results
                                                    </button>
                                                    <a href="{{ route('admin.report-cards.show', $reportCard->id) }}"
                                                       class="text-xs px-2 py-1 bg-neutral-600 hover:bg-neutral-700 text-white rounded transition-colors text-center">
                                                        Edit
                                                    </a>
                                                @else
                                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">No report card</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

         <div x-show="show" x-cloak>
            <x-ui.modal :maxWidth="'2xl'">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Report Card Preview</h3>
                        <button type="button" @click="show = false" class="text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <iframe :src="iframeSrc" title="Report card preview" class="w-full h-[80vh] border-0" loading="lazy"></iframe>
                </div>
            </x-ui.modal>
        </div>
    @empty
        <p class="text-neutral-500 dark:text-neutral-400">No classes found.</p>
    @endforelse

    @push('scripts')
    <script>
        function saveClassReportCard(classId) {
            var form = document.getElementById('class-form-' + classId);
            var saveBtn = event.target.closest('button');
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var formData = new FormData(form);

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            })
                .then(function(r) {
                    if (r.headers.get('content-type') && r.headers.get('content-type').indexOf('application/json') !== -1) {
                        return r.json();
                    }
                    return { success: true, message: 'Saved successfully.' };
                })
                .then(function(data) {
                    if (data.success) {
                        showStatus(data.message || 'Saved successfully!', 'success');
                    } else {
                        showStatus('Could not save: ' + (data.message || 'Please try again'), 'error');
                    }
                })
                .catch(function(err) { showStatus('Could not save: ' + err.message, 'error'); })
                .finally(function() {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save';
                });
        }

        function showStatus(message, type) {
            var existing = document.getElementById('status-msg');
            if (existing) existing.remove();
            var el = document.createElement('div');
            el.id = 'status-msg';
            el.className = 'fixed top-4 right-4 z-50 px-4 py-2 rounded shadow-lg text-sm font-medium ' +
                (type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white');
            el.textContent = message;
            document.body.appendChild(el);
            setTimeout(function() { el.remove(); }, 5000);
        }
    </script>
    @endpush
</x-layouts.app>
