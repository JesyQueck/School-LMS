<x-layouts.app title="Report Cards">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Report Cards</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage student report cards.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <div class="space-y-6">
        @foreach($classes as $class)
        <div class="border border-neutral-200 dark:border-dark-border rounded-lg overflow-hidden">
            <div class="bg-neutral-50 dark:bg-neutral-800 px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $class->name }}</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $class->students->count() }} students</p>
            </div>
            
            <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @foreach($class->students as $student)
                @php
                $reportCard = \App\Models\ReportCard::where('student_id', $student->id)
                    ->where('term_id', $currentTerm->id ?? null)
                    ->first();
                @endphp
                <div class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name }}</h4>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $student->admission_no }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($reportCard && $reportCard->is_published)
                                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">Published</span>
                            @elseif($reportCard && $reportCard->status === 'locked')
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">Locked</span>
                            @elseif($reportCard && $reportCard->status === 'approved')
                                <span class="text-xs font-semibold text-green-600 dark:text-green-400">Approved</span>
                            @elseif($reportCard && $reportCard->status === 'returned')
                                <span class="text-xs font-semibold text-red-600 dark:text-red-400">Returned</span>
                            @elseif($reportCard && in_array($reportCard->status, ['subject_scores_pending', 'pending_class_teacher_review']))
                                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">{{ ucwords(str_replace('_', ' ', $reportCard->status)) }}</span>
                            @elseif($reportCard && $reportCard->status === 'review')
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">Pending Principal Approval</span>
                            @elseif($reportCard && $reportCard->status === 'draft')
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Draft</span>
                            @else
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Not Generated</span>
                            @endif
                            
                            @if($reportCard && $reportCard->status !== 'locked' && !$reportCard->is_published)
                            <form method="POST" action="{{ route('admin.report-cards.approve', $reportCard->id) }}" onsubmit="return confirm('Approve this report card for {{ $student->full_name }}?');" style="display: inline;">
                                @csrf
                                <button type="submit" class="text-xs px-2 py-1 text-white bg-green-600 hover:bg-green-700 rounded">
                                    Approve
                                </button>
                            </form>
                            @endif
                            
                            <button type="button" 
                                    onclick="openReportCardModal({{ $student->id }}, '{{ $student->full_name }}', '{{ $student->admission_no }}', {{ $reportCard ? json_encode($reportCard) : 'null' }})"
                                    class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium text-sm">
                                View / Edit
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div id="report-card-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex justify-between items-center">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white" id="modal-title">Report Card</h2>
                <button type="button" onclick="closeReportCardModal()" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">&times;</button>
            </div>
            
            <form id="report-card-form" method="POST" action="{{ route('admin.report-cards.store') }}">
                @csrf
                <input type="hidden" id="student-id" name="student_id">
                <input type="hidden" id="term-id" name="term_id" value="{{ $currentTerm->id ?? '' }}">
                
                <div class="p-6 space-y-4">
                    <div>
                        <h3 id="modal-student-name" class="text-sm font-medium text-neutral-700 dark:text-neutral-300"></h3>
                        <p id="modal-admission-no" class="text-xs text-neutral-500 dark:text-neutral-400"></p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class Teacher Remark</label>
                            <textarea name="results[{{ 0 }}][class_teacher_remark]" rows="2" 
                                      class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                                      placeholder="Class teacher's comments..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Affective Domain</label>
                            <textarea name="results[{{ 0 }}][affective_domain]" rows="2" 
                                      class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                                      placeholder="Student's character, behavior, attitude..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Psychomotor Assessment</label>
                            <textarea name="results[{{ 0 }}][psychomotor_assessment]" rows="2" 
                                      class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                                      placeholder="Physical skills, coordination, practical work..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Health Remarks</label>
                            <textarea name="results[{{ 0 }}][health_remarks]" rows="2" 
                                      class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                                      placeholder="Health observations, absences, etc..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Principal Remark</label>
                            <textarea name="results[{{ 0 }}][principal_remark]" rows="2" 
                                      class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                                      placeholder="Principal's final comments..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Position in Class</label>
                                <input type="number" name="results[{{ 0 }}][position_in_class]" placeholder="e.g. 3" 
                                       class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Total in Class</label>
                                <input type="number" name="results[{{ 0 }}][total_students_in_class]" placeholder="e.g. 25" 
                                       class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-neutral-200 dark:border-dark-border flex justify-end gap-3">
                    <button type="button" onclick="closeReportCardModal()" class="px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 rounded-lg border border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm text-white rounded-lg bg-primary-600 hover:bg-primary-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="publish-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Publish All Report Cards</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                    This will publish all report cards for the current term. This action cannot be undone.
                </p>
            </div>
            <div class="px-6 py-4 border-t border-neutral-200 dark:border-dark-border flex justify-end gap-3">
                <button type="button" onclick="closePublishModal()" class="px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 rounded-lg border border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    Cancel
                </button>
                <form method="POST" action="{{ route('admin.report-cards.publish-all', $currentTerm->id) }}" id="publish-form">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm text-white rounded-lg bg-primary-600 hover:bg-primary-700">
                        Publish All
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openReportCardModal(studentId, studentName, admissionNo, reportCard) {
            document.getElementById('student-id').value = studentId;
            document.getElementById('modal-student-name').textContent = studentName;
            document.getElementById('modal-admission-no').textContent = admissionNo;
            
            if (reportCard) {
                document.querySelector(`[name="results\\[0\\]\\[class_teacher_remark\\"]`).value = reportCard.class_teacher_remark || '';
                document.querySelector(`[name="results\\[0\\]\\[principal_remark\\"]`).value = reportCard.principal_remark || '';
                document.querySelector(`[name="results\\[0\\]\\[affective_domain\\"]`).value = reportCard.affective_domain || '';
                document.querySelector(`[name="results\\[0\\]\\[psychomotor_assessment\\"]`).value = reportCard.psychomotor_assessment || '';
                document.querySelector(`[name="results\\[0\\]\\[health_remarks\\"]`).value = reportCard.health_remarks || '';
                document.querySelector(`[name="results\\[0\\]\\[position_in_class\\"]`).value = reportCard.position_in_class || '';
                document.querySelector(`[name="results\\[0\\]\\[total_students_in_class\\"]`).value = reportCard.total_students_in_class || '';
            } else {
                document.querySelectorAll('#report-card-form textarea').forEach(el => el.value = '');
                document.querySelectorAll('#report-card-form input[type="number"]').forEach(el => el.value = '');
            }
            
            document.getElementById('report-card-modal').classList.remove('hidden');
            document.getElementById('report-card-modal').classList.add('flex');
        }
        
        function closeReportCardModal() {
            document.getElementById('report-card-modal').classList.add('hidden');
            document.getElementById('report-card-modal').classList.remove('flex');
            document.getElementById('report-card-form').reset();
        }
        
        function closePublishModal() {
            document.getElementById('publish-modal').classList.add('hidden');
            document.getElementById('publish-modal').classList.remove('flex');
        }
    </script>
    @endpush
</x-layouts.app>