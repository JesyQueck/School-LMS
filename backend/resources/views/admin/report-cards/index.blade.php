<x-layouts.app title="Report Cards">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Principal Review</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Review and approve report cards submitted by class teachers.</p>
        </div>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <div class="space-y-6">
        @foreach($classes as $class)
        @php
            $pendingReportCards = \App\Models\ReportCard::whereIn('student_id', $class->students->pluck('id'))
                ->where('term_id', $currentTerm->id ?? null)
                ->where('status', 'approved')
                ->with('student.user')
                ->get();
            $publishedReportCards = \App\Models\ReportCard::whereIn('student_id', $class->students->pluck('id'))
                ->where('term_id', $currentTerm->id ?? null)
                ->where('status', \App\Models\ReportCard::STATUS_PUBLISHED)
                ->with('student.user')
                ->get();
        @endphp
        
        @if($pendingReportCards->count() > 0 || $publishedReportCards->count() > 0)
        <div class="border border-neutral-200 dark:border-dark-border rounded-lg overflow-hidden">
            <div class="bg-neutral-50 dark:bg-neutral-800 px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $class->name }}</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $pendingReportCards->count() }} approved, {{ $publishedReportCards->count() }} published</p>
            </div>
            
            <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @foreach($pendingReportCards as $reportCard)
                <div class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-neutral-900 dark:text-white">{{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}</h4>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $reportCard->student->admission_no ?? '' }}</p>
                        </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">Approved</span>
                        <button type="button" onclick="openReviewModal({{ $reportCard->id }}, '{{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}', '{{ $reportCard->student->admission_no ?? '' }}', {{ json_encode($reportCard) }})" class="text-xs px-2 py-1 text-neutral-600 dark:text-neutral-400 border border-neutral-300 dark:border-dark-border rounded hover:bg-neutral-100 dark:hover:bg-neutral-800">
                            Review
                        </button>
                        <form method="POST" action="{{ route('admin.report-cards.publish', $reportCard->id) }}" onsubmit="return confirm('Publish this report card for {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}?');" style="display: inline;">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 text-white bg-amber-600 hover:bg-amber-700 rounded">
                                Publish
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.report-cards.return', $reportCard->id) }}" onsubmit="return confirm('Return this report card for correction?');" style="display: inline;">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 text-white bg-red-600 hover:bg-red-700 rounded">
                                Return
                            </button>
                        </form>
                    </div>
                    </div>
                </div>
                @endforeach
                
                @foreach($publishedReportCards as $reportCard)
                <div class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors opacity-60">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-neutral-900 dark:text-white">{{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}</h4>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $reportCard->student->admission_no ?? '' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                             @if($reportCard->isPublished())
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">
                                    Published
                                </span>
                                <a href="{{ route('admin.report-cards.download', $reportCard->id) }}" class="text-xs px-2 py-1 text-neutral-600 dark:text-neutral-400 border border-neutral-300 dark:border-dark-border rounded hover:bg-neutral-100 dark:hover:bg-neutral-800">
                                    Download
                                </a>
                                <form method="POST" action="{{ route('admin.report-cards.unpublish', $reportCard->id) }}" onsubmit="return confirm('Unpublish this report card for {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}? This will allow corrections.');" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="text-xs px-2 py-1 text-white bg-red-600 hover:bg-red-700 rounded">
                                        Unpublish
                                    </button>
                                </form>
                            @else
                                <span class="text-xs font-semibold text-green-600 dark:text-green-400">Approved</span>
                                <form method="POST" action="{{ route('admin.report-cards.publish', $reportCard->id) }}" onsubmit="return confirm('Publish this report card for {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}?');" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="text-xs px-2 py-1 text-white bg-amber-600 hover:bg-amber-700 rounded">
                                        Publish
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>

    <div id="review-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex justify-between items-center">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white" id="review-modal-title">Principal Review</h2>
                <button type="button" onclick="closeReviewModal()" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">&times;</button>
            </div>
            
            <form id="review-form" method="POST" action="{{ route('admin.report-cards.approve', '__REPORT_CARD_ID__') }}">
                @csrf
                <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">
                
                <div class="p-6 space-y-4">
                    <div>
                        <h3 id="review-student-name" class="text-sm font-medium text-neutral-700 dark:text-neutral-300"></h3>
                        <p id="review-admission-no" class="text-xs text-neutral-500 dark:text-neutral-400"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Principal Remark</label>
                         <textarea name="principal_remark" rows="2" id="review-principal-remark"
                                   class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="Principal's final comments..."></textarea>
                     </div>

                     <div>
                         <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Promotion Decision</label>
                         <select name="promotion_decision" id="review-promotion" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                             <option value="">Select promotion status</option>
                             <option value="promoted">Promoted</option>
                             <option value="repeated">Repeated</option>
                             <option value="transferred">Transferred</option>
                         </select>
                     </div>
                </div>

                <div class="px-6 py-4 border-t border-neutral-200 dark:border-dark-border flex justify-end gap-3">
                    <button type="button" onclick="closeReviewModal()" class="px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 rounded-lg border border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm text-white rounded-lg bg-green-600 hover:bg-green-700">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openReviewModal(reportCardId, studentName, admissionNo, reportCard) {
            document.getElementById('review-student-name').textContent = studentName;
            document.getElementById('review-admission-no').textContent = admissionNo;
            document.getElementById('review-principal-remark').value = reportCard.principal_remark || '';

            document.getElementById('review-form').action = '/admin/report-cards/' + reportCardId + '/approve';

            document.getElementById('review-modal').classList.remove('hidden');
            document.getElementById('review-modal').classList.add('flex');
        }
        
        function closeReviewModal() {
            document.getElementById('review-modal').classList.add('hidden');
            document.getElementById('review-modal').classList.remove('flex');
        }
    </script>
    @endpush
</x-layouts.app>