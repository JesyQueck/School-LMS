<x-layouts.app title="Teacher Dashboard">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">Here's your teaching overview at Greenfield Academy.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <x-ui.stat-card label="Total Students" :value="$assignments->count() * 30" :trend="['direction' => 'neutral', 'value' => 'across your classes']" icon="users" />
        <x-ui.stat-card label="Subject Assignments" :value="$assignments->where('is_active', true)->count()" :trend="['direction' => 'neutral', 'value' => 'active subjects']" icon="book-open" />
        <x-ui.stat-card label="Pending Grading" value="8" :trend="['direction' => 'down', 'value' => '3 due today']" icon="clipboard-list" />
        @if($classAssignment)
            <x-ui.stat-card label="My Class" :value="$classAssignment->class->name ?? 'N/A'" :trend="['direction' => 'neutral', 'value' => 'Class Teacher']" icon="school" />
        @else
            <x-ui.stat-card label="Attendance" value="96%" :trend="['direction' => 'up', 'value' => '1.2% from last week']" icon="calendar-check" />
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">
                        @if($classAssignment)
                            Class: {{ $classAssignment->class->name ?? 'My Class' }}
                        @else
                            Today's Schedule
                        @endif
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
@if($classAssignment)
        <div class="border border-neutral-200 dark:border-dark-border rounded-lg overflow-hidden">
            <div class="bg-neutral-50 dark:bg-neutral-800 px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Score Submission Tracker</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $classAssignment->class->name ?? '' }} - {{ $classAssignment->term->name ?? '' }}</p>
            </div>
            <div class="p-4" id="submission-tracker">
                <div class="space-y-3" id="tracker-loading">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Loading submission status...</p>
                </div>
                <div id="tracker-content" class="hidden">
                    <div id="tracker-list" class="space-y-2"></div>
                    <div id="tracker-summary" class="mt-4 pt-3 border-t border-neutral-200 dark:border-dark-border">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-400">Progress:</span>
                            <span id="tracker-progress" class="font-medium text-neutral-900 dark:text-white"></span>
                        </div>
                        <div id="tracker-submit-btn" class="mt-3 hidden">
                            <a href="{{ route('teacher.report-cards.index') }}" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-center block">
                                Ready - Proceed to Report Cards
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-4">
            <x-ui.card elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Your Assignments</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if($assignments->where('is_active', true)->count() > 0)
                            @foreach($assignments->where('is_active', true) as $assignment)
                                <div class="flex items-start gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border">
                                    <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $assignment->classSubject->subject->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $assignment->classSubject->class->name ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No active subject assignments found.</p>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>
        
        @if($classAssignment)
        <x-ui.card>
            <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Completion Checklist</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Verify all requirements before submitting report cards</p>
            </div>
            <div class="p-4" id="completion-checklist">
                <div class="space-y-3" id="checklist-loading">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Checking requirements...</p>
                </div>
                <div id="checklist-content" class="hidden">
                    <ul class="space-y-2 text-sm" id="checklist-items"></ul>
                    <div id="checklist-submit-section" class="mt-4 pt-3 border-t border-neutral-200 dark:border-dark-border text-center hidden">
                        <a href="{{ route('teacher.report-cards.index') }}" id="checklist-submit-btn" class="inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            Proceed to Report Cards
                        </a>
                    </div>
                </div>
            </div>
        </x-ui.card>
        @endif
    </div>
    
    @push('scripts')
    @if($classAssignment)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trackerEl = document.getElementById('submission-tracker');
            const checklistEl = document.getElementById('completion-checklist');
            
            if (trackerEl) {
                fetch('{{ route('teacher.report-cards.progress') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    data.submission_progress.forEach(item => {
                        html += '<div class="flex items-center justify-between py-2">' +
                            '<div class="flex items-center gap-2">' +
                                '<span class="text-sm text-neutral-700 dark:text-neutral-300">' + item.subject + '</span>' +
                                '<span class="text-xs text-neutral-500 dark:text-neutral-400">(' + item.teacher + ')</span>' +
                            '</div>' +
                            '<span class="text-lg">' + (item.completed ? '✔' : '❌') + '</span>' +
                        '</div>';
                    });
                    document.getElementById('tracker-list').innerHTML = html;
                    document.getElementById('tracker-progress').textContent = 
                        data.all_scores_submitted ? 'All scores submitted!' : 
                        (data.total_subjects - data.submission_progress.filter(p => !p.completed).length) + '/' + data.total_subjects + ' subjects submitted';
                    
                    const submitBtn = document.getElementById('tracker-submit-btn');
                    if (data.is_ready_to_submit) {
                        submitBtn.classList.remove('hidden');
                    }
                    
                    document.getElementById('tracker-loading').classList.add('hidden');
                    document.getElementById('tracker-content').classList.remove('hidden');
                })
                .catch(err => {
                    document.getElementById('tracker-loading').innerHTML = 
                        '<p class="text-sm text-red-500">Error loading submission status</p>';
                });
            }
            
            if (checklistEl) {
                fetch('{{ route('teacher.report-cards.progress') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    let items = '';
                    items += '<li class="flex items-center gap-2"><span class="text-green-500">' + (data.all_scores_submitted ? '✔' : '❌') + '</span> All subject scores submitted</li>';
                    items += '<li class="flex items-center gap-2"><span class="text-green-500">' + (data.attendance_submitted ? '✔' : '❌') + '</span> Attendance generated</li>';
                    items += '<li class="flex items-center gap-2"><span class="text-green-500">' + (data.comments_completed ? '✔' : '❌') + '</span> Grades calculated</li>';
                    items += '<li class="flex items-center gap-2"><span class="text-green-500">' + (data.comments_completed ? '✔' : '❌') + '</span> Average calculated</li>';
                    items += '<li class="flex items-center gap-2"><span class="text-green-500">' + (data.comments_completed ? '✔' : '❌') + '</span> Affective Domain completed</li>';
                    items += '<li class="flex items-center gap-2"><span class="text-green-500">' + (data.comments_completed ? '✔' : '❌') + '</span> Psychomotor Assessment completed</li>';
                    items += '<li class="flex items-center gap-2"><span class="text-green-500">' + (data.comments_completed ? '✔' : '❌') + '</span> Teacher Comment entered</li>';
                    
                    document.getElementById('checklist-items').innerHTML = items;
                    
                    if (data.is_ready_to_submit) {
                        document.getElementById('checklist-submit-section').classList.remove('hidden');
                    }
                    
                    document.getElementById('checklist-loading').classList.add('hidden');
                    document.getElementById('checklist-content').classList.remove('hidden');
                })
                .catch(err => {
                    document.getElementById('checklist-loading').innerHTML = 
                        '<p class="text-sm text-red-500">Error loading checklist</p>';
                });
            }
        });
    </script>
    @endif
    @endpush