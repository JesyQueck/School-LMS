<x-layouts.app title="My Report Cards">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/student/dashboard">Student</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Report Cards</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Report Cards</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View and download your published academic performance records.</p>
    </div>

    @forelse($reportCards as $sessionName => $sessionCards)
        <x-ui.card class="mb-6">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ $sessionName }} Academic Session</h2>
            </div>
            <div class="p-6 space-y-6">
                @foreach($sessionCards->groupBy(fn ($rc) => $rc->term->name ?? 'Unknown Term') as $termName => $termCards)
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mb-3">{{ $termName }}</h3>
                        @foreach($termCards as $reportCard)
                            <div class="flex items-center justify-between p-4 border border-neutral-200 dark:border-dark-border rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Published</span>
                                        <span class="text-xs text-neutral-400 dark:text-neutral-500">Class: {{ $reportCard->student->class->name ?? 'N/A' }}</span>
                                        <span class="text-xs text-neutral-400 dark:text-neutral-500">Position: {{ $reportCard->position_in_class ?? 'N/A' }} of {{ $reportCard->total_students_in_class ?? 'N/A' }}</span>
                                    </div>
                                    <h4 class="text-sm font-medium text-neutral-900 dark:text-white">{{ $reportCard->term->name ?? 'Report Card' }}</h4>
                                    @if($reportCard->class_teacher_remark)
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 italic">"{{ $reportCard->class_teacher_remark }}"</p>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('student.report-cards.download', $reportCard) }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @empty
        <x-ui.card>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2z"/></svg>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No published report cards found.</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Your report cards will appear here once published by your teachers.</p>
            </div>
        </x-ui.card>
    @endforelse
</x-layouts.app>
