<x-layouts.app title="Report Cards">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Report Cards</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Review and provide comments for student report cards.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    @if($classAssignment && $students->count() > 0)
    <div class="space-y-3">
        @foreach($students as $student)
            @php
                $reportCard = $reportCards->firstWhere('student_id', $student->id);
                $hasScores = $student->results()->where('term_id', $term->id)->exists();
            @endphp
            <div class="flex items-center justify-between p-4 border border-neutral-200 dark:border-dark-border rounded-lg">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name }}</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $student->admission_no }}</p>
                    @if($reportCard && $reportCard->class_teacher_remark)
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">Remark: {{ \Illuminate\Support\Str::limit($reportCard->class_teacher_remark, 50) }}</p>
                    @endif
                </div>
                
                <div class="flex items-center gap-2">
                    @if($reportCard && $reportCard->isPublished())
                        <span class="text-xs px-2 py-1 rounded border bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800">Published</span>
                    @elseif($reportCard && $reportCard->status === 'approved')
                        <span class="text-xs px-2 py-1 rounded border bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 border-blue-200 dark:border-blue-800">Submitted</span>
                    @else
                        <span class="text-xs px-2 py-1 rounded border bg-gray-100 dark:bg-gray-800/30 text-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-800">Draft</span>
                    @endif
                    
                    @if($hasScores)
                    <button type="button" onclick="openCommentModal({{ $student->id }}, '{{ addslashes($student->full_name) }}', '{{ $student->admission_no }}', {{ $reportCard ? json_encode($reportCard) : 'null' }})" class="px-3 py-1 text-xs text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30">
                        Add Comment
                    </button>
                    <button type="button" onclick="loadPreviewData({{ $student->id }})" class="px-3 py-1 text-xs text-secondary-600 dark:text-secondary-400 border border-secondary-600 dark:border-secondary-400 rounded hover:bg-secondary-50 dark:hover:bg-secondary-900/30">
                        Preview
                    </button>
                    @if($reportCard && $reportCard->status !== 'approved' && ! $reportCard->isPublished())
                        <form method="POST" action="{{ route('teacher.report-cards.submit', $reportCard->id) }}" id="submit-form-{{ $student->id }}" style="display: inline;">
                            @csrf
                            <button type="submit" onclick="return confirm('Submit report card for {{ $student->full_name }}?')" class="px-3 py-1 text-xs text-white bg-green-600 hover:bg-green-700 rounded">
                                Submit
                            </button>
                        </form>
                    @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Comment Modal -->
    <div id="comment-modal" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border flex justify-between items-center">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Add Comment</h2>
                <button type="button" onclick="closeCommentModal()" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">&times;</button>
            </div>
            <form method="POST" action="{{ route('teacher.report-cards.store') }}">
                @csrf
                <input type="hidden" id="student-id" name="student_id">
                <input type="hidden" name="term_id" value="{{ $term->id ?? '' }}">
                <div class="p-4 space-y-4">
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3" id="modal-student-name"></p>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class Teacher Remark</label>
                        <textarea name="comment" rows="2" id="comment-input" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-sm"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Affective Domain</label>
                        <textarea name="affective_domain" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-sm" placeholder="Student's character, behavior, attitude..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Psychomotor Assessment</label>
                        <textarea name="psychomotor_assessment" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-sm" placeholder="Physical skills, coordination, practical work..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Health Remarks</label>
                        <textarea name="health_remarks" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-sm" placeholder="Health observations, absences, etc..."></textarea>
                    </div>
                </div>
                <div class="px-4 py-3 border-t border-neutral-200 dark:border-dark-border flex justify-end gap-2">
                    <button type="button" onclick="closeCommentModal()" class="px-3 py-1 text-sm text-neutral-700 dark:text-neutral-300 rounded border border-neutral-300 dark:border-dark-border">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded">
                        Save Comment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="preview-modal" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-50 overflow-y-auto">
        <div id="preview-content" class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col" style="font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px;">
            <!-- Header -->
            <div class="px-4 py-3 border-b" style="border-color: #e5e7eb;">
                <h2 class="text-lg font-semibold" style="color: #000000;">Report Card Preview</h2>
            </div>
            
            <div class="p-4 overflow-y-auto">
                <!-- School Header -->
                <div class="flex items-start justify-between gap-4 pb-4 border-b" style="border-color: #16324F;">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/Logo.webp') }}" alt="{{ config('school.name', 'Greenfield Academy') }}" class="w-12 h-12 flex-shrink-0 object-contain">
                        <div style="min-width: 0;">
                            <h1 class="font-display text-base font-bold tracking-wide" style="color: #16324F; margin: 0;">{{ config('school.name', 'Greenfield Academy') }}</h1>
                            <p class="text-[11px] text-gray-600 mt-0.5 mb-0" style="margin: 0;">{{ config('school.address', '123 Education Lane, Victoria Island, Lagos') }}</p>
                            <p class="text-[11px] text-gray-600 mb-0" style="margin: 0;">{{ config('school.email', 'info@greenfieldacademy.edu') }} | {{ config('school.phone', '+234 800 000 0000') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span style="display: inline-block; padding: 2px 10px; font-size: 10px; font-weight: 700; border: 1px solid #1B6B3A; border-radius: 4px; color: #1B6B3A; background-color: #dcfce7;">
                            PUBLISHED
                        </span>
                    </div>
                </div>

                <!-- Title -->
                <div class="text-center mt-4 mb-5">
                    <p class="uppercase text-sm font-bold tracking-[0.25em]" style="color: #059669;">Terminal Report Sheet</p>
                    <p class="text-xs text-gray-600 mt-1" id="preview-term-line"></p>
                </div>

                <!-- Student Info -->
                <div class="grid grid-cols-2 gap-x-10 gap-y-2 text-[13px] mb-6">
                    <div>
                        <div style="display: flex; align-items: baseline; gap: 8px;">
                            <span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Name:</span>
                            <span id="preview-name" class="font-semibold" style="color: #000000;"></span>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; align-items: baseline; gap: 8px;">
                            <span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Admission No:</span>
                            <span id="preview-admission" class="font-semibold" style="color: #000000;"></span>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; align-items: baseline; gap: 8px;">
                            <span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Class:</span>
                            <span id="preview-class" class="font-semibold" style="color: #000000;"></span>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; align-items: baseline; gap: 8px;">
                            <span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Date of Birth:</span>
                            <span id="preview-dob" class="font-semibold" style="color: #000000;"></span>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <table class="results w-full border-collapse text-[12px] mb-4">
                    <thead>
                        <tr style="background-color: #059669; color: white;">
                            <th class="text-left py-2 px-3 uppercase text-[10px]">Subject</th>
                            <th class="py-2 px-2 uppercase text-[10px] w-16">CA</th>
                            <th class="py-2 px-2 uppercase text-[10px] w-16">Exam</th>
                            <th class="py-2 px-2 uppercase text-[10px] w-16">Total</th>
                            <th class="py-2 px-2 uppercase text-[10px] w-16">Grade</th>
                            <th class="text-left py-2 px-3 uppercase text-[10px]">Remark</th>
                        </tr>
                    </thead>
                    <tbody id="preview-results">
                        <tr><td colspan="6" class="py-2 px-3 text-center" style="color: #6b7280;">Loading...</td></tr>
                    </tbody>
                </table>

                <!-- Position and Attendance -->
                <div id="preview-summary" class="text-[11px] mb-4 space-y-1">
                    <p><strong>Position in Class:</strong> <span id="preview-position" style="font-weight: 500; color: #000000;">—</span> of <span id="preview-total" style="font-weight: 500; color: #000000;">—</span></p>
                    <p><strong>Average Percentage:</strong> <span id="preview-avg" style="font-weight: 500; color: #000000;">0</span>%</p>
                    <p><strong>Attendance:</strong> <span id="preview-attendance" style="font-weight: 500; color: #000000;">0/0 days present</span></p>
                </div>

                <!-- Remarks -->
                <div class="space-y-3 mb-4">
                    <div class="border p-3" style="border-color: #C9CDD3;">
                        <p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Class Teacher's Remark</p>
                        <p id="preview-remark-content" class="text-[12px] italic min-h-[2.5em]" style="color: #000000;">—</p>
                        <p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: #C9CDD3;">
                            Signature: ______________________ &nbsp; Date: ____________
                        </p>
                    </div>
                    
                    <div class="border p-3" style="border-color: #C9CDD3;">
                        <p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Affective Domain</p>
                        <p id="preview-affective-domain" class="text-[12px] italic min-h-[2em]" style="color: #000000;">—</p>
                    </div>
                    
                    <div class="border p-3" style="border-color: #C9CDD3;">
                        <p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Psychomotor Assessment</p>
                        <p id="preview-psychomotor" class="text-[12px] italic min-h-[2.5em]" style="color: #000000;">—</p>
                    </div>
                    
                    <div class="border p-3" style="border-color: #C9CDD3;">
                        <p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Health Remarks</p>
                        <p id="preview-health-remarks" class="text-[12px] italic min-h-[2em]" style="color: #000000;">—</p>
                    </div>
                    
                    <div class="border p-3" style="border-color: #C9CDD3;">
                        <p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Principal's Remark</p>
                        <p id="preview-principal-remark" class="text-[12px] italic min-h-[2.5em]" style="color: #000000;">—</p>
                        <p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: #C9CDD3;">
                            Signature: ______________________ &nbsp; Date: ____________
                        </p>
                    </div>
                </div>

                <!-- Grading Key -->
                <div class="border p-3 mb-4" style="border-color: #C9CDD3; background-color: #F9F8F3;">
                    <div class="flex justify-center gap-3 text-[9px] flex-wrap">
                        <span class="font-semibold uppercase tracking-wide" style="color: #16324F;">Grading Key:</span>
                        <span>A1 (75–100) Excellent</span>
                        <span>&middot;</span>
                        <span>B2 (70–74) V.Good</span>
                        <span>&middot;</span>
                        <span>B3 (65–69) Good</span>
                        <span>&middot;</span>
                        <span>C4–C6 (50–64) Credit</span>
                        <span>&middot;</span>
                        <span>D7 (45–49) Pass</span>
                        <span>&middot;</span>
                        <span>E8 (40–44) Pass</span>
                        <span>&middot;</span>
                        <span style="color: #A3312B;">F9 (0–39) Fail</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-between items-center pt-3 border-t text-[10px]" style="border-color: #16324F; color: #6b7280;">
                    <span>Next Term Begins: <span id="preview-next-term" class="font-semibold" style="color: #000000;">TBA</span></span>
                    <span class="text-center">&copy; {{ date('Y') }} Greenfield Academy</span>
                </div>
            </div>
            
            <div class="px-4 py-3 border-t" style="border-color: #e5e7eb;">
                <button type="button" onclick="closePreviewModal()" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; color: #6b7280; background-color: #f9fafb; font-weight: 500; border-radius: 4px; cursor: pointer;">
                    Close Preview
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openCommentModal(studentId, studentName, admissionNo, reportCard) {
            document.getElementById('student-id').value = studentId;
            document.getElementById('modal-student-name').textContent = studentName + ' (' + admissionNo + ')';
            if (reportCard && reportCard.class_teacher_remark) {
                document.getElementById('comment-input').value = reportCard.class_teacher_remark;
            } else {
                document.getElementById('comment-input').value = '';
            }
            document.getElementById('comment-modal').classList.remove('hidden');
            document.getElementById('comment-modal').classList.add('flex');
        }
        
        function closeCommentModal() {
            document.getElementById('comment-modal').classList.add('hidden');
            document.getElementById('comment-modal').classList.remove('flex');
        }
        
        function loadPreviewData(studentId) {
            fetch("/teacher/report-cards/student/" + studentId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    const student = data.student || {};
                    const term = data.term || {};
                    const results = data.results || [];
                    const reportCard = data.reportCard || {};

                    document.getElementById('preview-name').textContent = student.name || student.full_name || 'N/A';
                    document.getElementById('preview-admission').textContent = student.admission_no || 'N/A';
                    document.getElementById('preview-class').textContent = student.class?.name || 'N/A';
                    document.getElementById('preview-dob').textContent = student.date_of_birth ? 
                        new Date(student.date_of_birth).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
                    document.getElementById('preview-term-line').textContent = 
                        (term?.name || 'Term') + (term?.session ? ' · ' + term.session + ' Session' : '');
                    
                    let gradeColors = {
                        'A1': '#000000',
                        'B2': '#000000',
                        'B3': '#000000',
                        'C4': '#000000',
                        'C5': '#000000',
                        'C6': '#000000',
                        'D7': '#A3312B',
                        'E8': '#A3312B',
                        'F9': '#A3312B'
                    };

                    let resultsHtml = '';
                    if (results.length > 0) {
                        results.forEach(r => {
                            const gradeColor = gradeColors[r.grade] || '#000000';
                            resultsHtml += '<tr>' +
                                '<td class="px-3 py-2" style="border: 1px solid #C9CDD3; color: #000000;">' + (r.subject || '—') + '</td>' +
                                '<td class="px-2 py-2 text-center" style="border: 1px solid #C9CDD3; color: #000000;">' + (r.ca ?? 'N/A') + '</td>' +
                                '<td class="px-2 py-2 text-center" style="border: 1px solid #C9CDD3; color: #000000;">' + (r.exam ?? 'N/A') + '</td>' +
                                '<td class="px-2 py-2 text-center font-semibold" style="border: 1px solid #C9CDD3; color: #000000;">' + (r.total ?? 'N/A') + '</td>' +
                                '<td class="px-2 py-2 text-center font-bold" style="border: 1px solid #C9CDD3; color: ' + gradeColor + ';">' + (r.grade ?? 'N/A') + '</td>' +
                                '<td class="px-3 py-2" style="border: 1px solid #C9CDD3; color: #000000;">' + (r.remark ?? 'N/A') + '</td>' +
                            '</tr>';
                        });
                    } else {
                        resultsHtml = '<tr><td colspan="6" class="py-2 px-3 text-center" style="color: #6b7280;">No results available for this term.</td></tr>';
                    }
                    document.getElementById('preview-results').innerHTML = resultsHtml;
                    
                    document.getElementById('preview-position').textContent = reportCard.position_in_class || '—';
                    document.getElementById('preview-total').textContent = reportCard.total_students_in_class || '—';
                    
                    let avg = 0;
                    if (results.length > 0) {
                        const total = results.reduce((sum, r) => sum + (r.total || 0), 0);
                        avg = Math.round(total / results.length);
                    }
                    document.getElementById('preview-avg').textContent = avg;
                    
                    document.getElementById('preview-attendance').textContent = data.attendance || '0/0 days present';
                    document.getElementById('preview-next-term').textContent = 
                        (reportCard.next_term_begins ? new Date(reportCard.next_term_begins).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'TBA');
                    
                    document.getElementById('preview-remark-content').textContent = reportCard.class_teacher_remark || '—';
                    document.getElementById('preview-affective-domain').textContent = reportCard.affective_domain || '—';
                    document.getElementById('preview-psychomotor').textContent = reportCard.psychomotor_assessment || '—';
                    document.getElementById('preview-health-remarks').textContent = reportCard.health_remarks || '—';
                    document.getElementById('preview-principal-remark').textContent = reportCard.principal_remark || '—';
                    
                    document.getElementById('preview-modal').classList.remove('hidden');
                    document.getElementById('preview-modal').classList.add('flex');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading results: ' + error.message);
                    document.getElementById('preview-results').innerHTML = 
                        '<tr><td colspan="6" class="py-2 px-3 text-center" style="color: #ef4444;">Error loading results</td></tr>';
                    document.getElementById('preview-modal').classList.remove('hidden');
                    document.getElementById('preview-modal').classList.add('flex');
                });
        }
        
        function closePreviewModal() {
            document.getElementById('preview-modal').classList.add('hidden');
            document.getElementById('preview-modal').classList.remove('flex');
        }
    </script>
    @endpush
</x-layouts.app>