<x-layouts.app title="My Report Cards">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/student/dashboard">Student</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Report Cards</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Report Cards</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">View and download your academic performance records.</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($reportCards as $reportCard)
            <x-ui.card>
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Published</span>
                                <span class="text-xs text-neutral-400 dark:text-neutral-500">{{ $reportCard->term->name ?? 'N/A' }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $reportCard->term->name ?? 'Report Card' }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Position: {{ $reportCard->position_in_class ?? 'N/A' }} of {{ $reportCard->total_students_in_class ?? 'N/A' }}</p>
                            @if($reportCard->class_teacher_remark)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2 italic">"{{ $reportCard->class_teacher_remark }}"</p>
                            @endif
                        </div>
                        <a href="{{ route('student.report-cards.download', $reportCard) }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="No report cards yet" description="Your report cards will appear here once published by your teachers. Check back later." />
        @endforelse
    </div>
</x-layouts.app>
