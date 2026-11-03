<x-layouts.app title="Report Cards">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Report Cards</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage and review report cards for your assigned class.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('teacher.report-cards.progress') }}" class="px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">
                    View Submission Progress
                </a>
            </div>
        </div>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert variant="error" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <div class="flex-1 text-sm font-medium text-danger-800 dark:text-danger-300">{{ session('error') }}</div>
            </div>
        </x-ui.alert>
    @endif

    @if($classAssignment)
        {{-- Filters Bar --}}
        <x-ui.card class="mb-6">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filters</h3>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="filter-status" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                    <select id="filter-status" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div>
                    <label for="filter-search" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Search</label>
                    <input type="text" id="filter-search" placeholder="Name or admission no..." class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">&nbsp;</label>
                    <div class="flex items-end">
                        <button id="btn-export" class="px-3 py-2 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded transition-colors">
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Class/Term Info --}}
        <div class="mb-4 p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-primary-700 dark:text-primary-300 uppercase tracking-wide">Class</p>
                    <p class="font-semibold text-primary-900 dark:text-primary-100">{{ $classAssignment->class->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-primary-700 dark:text-primary-300 uppercase tracking-wide">Term</p>
                    <p class="font-semibold text-primary-900 dark:text-primary-100">{{ $term->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-primary-700 dark:text-primary-300 uppercase tracking-wide">Students</p>
                    <p class="font-semibold text-primary-900 dark:text-primary-100">{{ $students->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Report Cards Table --}}
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Student Report Cards</h3>
                <div class="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
                    <span id="visible-count">{{ $students->count() }}</span> of {{ $students->count() }} students
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Admission No</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Scores</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Comments</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border" id="report-cards-tbody">
                        @forelse($students as $student)
                            @php
                                $reportCard = $reportCards->firstWhere('student_id', $student->id);
                                $hasScores = $student->results()->where('term_id', $term->id)->whereNotNull('ca_score')->whereNotNull('exam_score')->exists();
                                $statusClass = match($reportCard->status ?? 'draft') {
                                    'published' => 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300 border-success-200 dark:border-success-800',
                                    'approved' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                    'submitted' => 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-primary-200 dark:border-primary-800',
                                    default => 'bg-neutral-100 dark:bg-neutral-800/30 text-neutral-700 dark:text-neutral-300 border-neutral-200 dark:border-neutral-800',
                                };
                            @endphp
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors" data-name="{{ strtolower($student->full_name) }}" data-admission="{{ strtolower($student->admission_no) }}" data-status="{{ $reportCard->status ?? 'draft' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($student->user && $student->user->profile_photo)
                                            <img src="{{ asset('storage/' . $student->user->profile_photo) }}" alt="" class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-sm">
                                                {{ substr($student->full_name ?? $student->admission_no, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $student->admission_no }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($hasScores)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300">
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Complete
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300">
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Pending
                                        </span>
                                    @endif
                                </td>
<td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusClass }}">
                                        @if($reportCard && $reportCard->status === 'published')
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($reportCard && $reportCard->status === 'approved')
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif($reportCard && $reportCard->status === 'submitted')
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @else
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                        {{ ucfirst($reportCard->status ?? 'draft') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($reportCard && ($reportCard->class_teacher_remark || $reportCard->affective_domain || $reportCard->psychomotor_assessment || $reportCard->health_remarks))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            Added
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400">
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                            None
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($hasScores)
                                            <button type="button" onclick="openCommentModal({{ $student->id }}, '{{ addslashes($student->full_name) }}', '{{ $student->admission_no }}', {{ $reportCard ? json_encode($reportCard) : 'null' }})" class="px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">
                                                {{ $reportCard && ($reportCard->class_teacher_remark || $reportCard->affective_domain || $reportCard->psychomotor_assessment || $reportCard->health_remarks) ? 'Edit Comment' : 'Add Comment' }}
                                            </button>
                                            <button type="button" onclick="loadPreviewData({{ $student->id }})" class="px-3 py-1.5 text-xs font-medium text-secondary-600 dark:text-secondary-400 border border-secondary-600 dark:border-secondary-400 rounded hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                                                Preview
                                            </button>
                                            @if($reportCard && $reportCard->status !== 'approved' && !$reportCard->isPublished())
                                                <form method="POST" action="{{ route('teacher.report-cards.submit', $reportCard->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Submit report card for {{ $student->full_name }}?')" class="px-3 py-1.5 text-xs font-medium text-white bg-success-600 hover:bg-success-700 rounded transition-colors">
                                                        Submit
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No students in this class.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @else
        <x-ui.card>
            <div class="p-12 text-center">
                <svg class="h-16 w-16 text-neutral-400 dark:text-neutral-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">No Class Assignment</h3>
                <p class="text-neutral-500 dark:text-neutral-400 mb-4">You are not assigned as a class teacher for the current academic session.</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Report cards can only be managed by the assigned class teacher.</p>
            </div>
        </x-ui.card>
    @endif

    {{-- Comment Modal --}}
    <div id="comment-modal" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border flex justify-between items-center">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Add Comment</h2>
                <button type="button" onclick="closeCommentModal()" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 text-2xl leading-none">&times;</button>
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
                    <button type="button" onclick="closeCommentModal()" class="px-3 py-1 text-sm text-neutral-700 dark:text-neutral-300 rounded border border-neutral-300 dark:border-dark-border transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded transition-colors">
                        Save Comment
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div id="preview-modal" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-50 overflow-y-auto">
        <div id="preview-content" class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col" style="font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px;">
            <div class="px-4 py-3 border-b" style="border-color: #e5e7eb;">
                <h2 class="text-lg font-semibold" style="color: #000000;">Report Card Preview</h2>
            </div>
            <div class="p-4 overflow-y-auto">
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
                        <span style="display: inline-block; padding: 2px 10px; font-size: 10px; font-weight: 700; border: 1px solid #1B6B3A; border-radius: 4px; color: #1B6B3A; background-color: #dcfce7;">PUBLISHED</span>
                    </div>
                </div>

                <div class="text-center mt-4 mb-5">
                    <p class="uppercase text-sm font-bold tracking-[0.25em]" style="color: #059669;">Terminal Report Sheet</p>
                    <p class="text-xs text-gray-600 mt-1" id="preview-term-line"></p>
                </div>

                <div class="flex items-start gap-4 mb-6">
                    <div class="flex-shrink-0">
                        <div id="preview-photo" class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-xl">
                            ?
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="grid grid-cols-2 gap-x-10 gap-y-2 text-[13px]">
                            <div><div style="display: flex; align-items: baseline; gap: 8px;"><span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Name:</span><span id="preview-name" class="font-semibold" style="color: #000000;"></span></div></div>
                            <div><div style="display: flex; align-items: baseline; gap: 8px;"><span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Admission No:</span><span id="preview-admission" class="font-semibold" style="color: #000000;"></span></div></div>
                            <div><div style="display: flex; align-items: baseline; gap: 8px;"><span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Class:</span><span id="preview-class" class="font-semibold" style="color: #000000;"></span></div></div>
                            <div><div style="display: flex; align-items: baseline; gap: 8px;"><span class="uppercase text-[10px] tracking-wide" style="color: #6b7280;">Date of Birth:</span><span id="preview-dob" class="font-semibold" style="color: #000000;"></span></div></div>
                        </div>
                    </div>
                </div>

                <table class="results w-full border-collapse text-[12px] mb-4">
                    <thead><tr style="background-color: #059669; color: white;"><th class="text-left py-2 px-3 uppercase text-[10px]">Subject</th><th class="py-2 px-2 uppercase text-[10px] w-16">CA</th><th class="py-2 px-2 uppercase text-[10px] w-16">Exam</th><th class="py-2 px-2 uppercase text-[10px] w-16">Total</th><th class="py-2 px-2 uppercase text-[10px] w-16">Grade</th><th class="text-left py-2 px-3 uppercase text-[10px]">Remark</th></tr></thead>
                    <tbody id="preview-results"><tr><td colspan="6" class="py-2 px-3 text-center" style="color: #6b7280;">Loading...</td></tr></tbody>
                </table>

                <div id="preview-summary" class="text-[11px] mb-4 space-y-1">
                    <p><strong>Position in Class:</strong> <span id="preview-position" style="font-weight: 500; color: #000000;">—</span> of <span id="preview-total" style="font-weight: 500; color: #000000;">—</span></p>
                    <p><strong>Average Percentage:</strong> <span id="preview-avg" style="font-weight: 500; color: #000000;">0</span>%</p>
                    <p><strong>Attendance:</strong> <span id="preview-attendance" style="font-weight: 500; color: #000000;">0/0 days present</span></p>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="border p-3" style="border-color: #C9CDD3;"><p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Class Teacher's Remark</p><p id="preview-remark-content" class="text-[12px] italic min-h-[2.5em]" style="color: #000000;">—</p><p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: #C9CDD3;">Signature: ______________________ &nbsp; Date: ____________</p></div>
                    <div class="border p-3" style="border-color: #C9CDD3;"><p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Affective Domain</p><p id="preview-affective-domain" class="text-[12px] italic min-h-[2em]" style="color: #000000;">—</p></div>
                    <div class="border p-3" style="border-color: #C9CDD3;"><p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Psychomotor Assessment</p><p id="preview-psychomotor" class="text-[12px] italic min-h-[2.5em]" style="color: #000000;">—</p></div>
                    <div class="border p-3" style="border-color: #C9CDD3;"><p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Health Remarks</p><p id="preview-health-remarks" class="text-[12px] italic min-h-[2em]" style="color: #000000;">—</p></div>
                    <div class="border p-3" style="border-color: #C9CDD3;"><p class="uppercase text-[10px] tracking-wide" style="color: #6b7280; margin: 0 0 8px 0;">Principal's Remark</p><p id="preview-principal-remark" class="text-[12px] italic min-h-[2.5em]" style="color: #000000;">—</p><p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: #C9CDD3;">Signature: ______________________ &nbsp; Date: ____________</p></div>
                </div>

                <div class="border p-3 mb-4" style="border-color: #C9CDD3; background-color: #F9F8F3;"><div class="flex justify-center gap-3 text-[9px] flex-wrap"><span class="font-semibold uppercase tracking-wide" style="color: #16324F;">Grading Key:</span><span>A1 (75–100) Excellent</span><span>&middot;</span><span>B2 (70–74) V.Good</span><span>&middot;</span><span>B3 (65–69) Good</span><span>&middot;</span><span>C4–C6 (50–64) Credit</span><span>&middot;</span><span>D7 (45–49) Pass</span><span>&middot;</span><span>E8 (40–44) Pass</span><span>&middot;</span><span style="color: #A3312B;">F9 (0–39) Fail</span></div></div>

                <div class="flex justify-between items-center pt-3 border-t text-[10px]" style="border-color: #16324F; color: #6b7280;"><span>Next Term Begins: <span id="preview-next-term" class="font-semibold" style="color: #000000;">TBA</span></span><span class="text-center">&copy; {{ date('Y') }} Greenfield Academy</span></div>
            </div>
            <div class="px-4 py-3 border-t" style="border-color: #e5e7eb;"><button type="button" onclick="closePreviewModal()" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; color: #6b7280; background-color: #f9fafb; font-weight: 500; border-radius: 4px; cursor: pointer;">Close Preview</button></div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('filter-status');
            const searchFilter = document.getElementById('filter-search');
            const rows = document.querySelectorAll('#report-cards-tbody tr[data-name]');
            const visibleCount = document.getElementById('visible-count');

            function applyFilters() {
                const statusVal = statusFilter.value;
                const searchVal = searchFilter.value.toLowerCase();
                let count = 0;

                rows.forEach(row => {
                    const name = row.dataset.name;
                    const admission = row.dataset.admission;
                    const status = row.dataset.status;
                    const matchesStatus = !statusVal || status === statusVal;
                    const matchesSearch = !searchVal || name.includes(searchVal) || admission.includes(searchVal);
                    const show = matchesStatus && matchesSearch;
                    row.style.display = show ? '' : 'none';
                    if (show) count++;
                });

                if (visibleCount) visibleCount.textContent = count;
            }

            statusFilter?.addEventListener('change', applyFilters);
            searchFilter?.addEventListener('input', applyFilters);
        });

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
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    
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

                    // Student photo
                    const photoEl = document.getElementById('preview-photo');
                    if (student.profile_photo) {
                        photoEl.innerHTML = '<img src="/storage/' + student.profile_photo + '" alt="" class="w-full h-full rounded-full object-cover">';
                    } else {
                        photoEl.innerHTML = (student.name || student.full_name || student.admission_no || '?').charAt(0).toUpperCase();
                        photoEl.style.fontSize = '24px';
                    }
                    
                    let gradeColors = {
                        'A1': '#000000', 'B2': '#000000', 'B3': '#000000',
                        'C4': '#000000', 'C5': '#000000', 'C6': '#000000',
                        'D7': '#A3312B', 'E8': '#A3312B', 'F9': '#A3312B'
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
                    const resultsBody = document.getElementById('preview-results');
                    const errorRow = document.createElement('tr');
                    const errorCell = document.createElement('td');
                    errorCell.colSpan = 6;
                    errorCell.className = 'py-2 px-3 text-center text-danger-600 dark:text-danger-400';
                    errorCell.setAttribute('role', 'alert');
                    errorCell.textContent = 'Error loading results: ' + error.message;
                    errorRow.appendChild(errorCell);
                    resultsBody.replaceChildren(errorRow);
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