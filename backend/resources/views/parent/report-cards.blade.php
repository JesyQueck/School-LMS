<x-layouts.app title="Report Cards">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/parent/dashboard">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('parent.children.show', $student->id) }}">{{ $student->full_name ?? 'Child' }}</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Report Cards</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">{{ $student->full_name ?? 'Student' }} - Report Cards</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $student->schoolClass->name ?? 'N/A' }} &middot; {{ $student->admission_no ?? 'N/A' }}</p>
    </div>

    <x-ui.card>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Report Cards</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Published report cards are available to view and download. Unpublished terms are not shown.</p>
        </div>
        <div class="p-6">
            @forelse($terms as $term)
                @php $reportCard = $term->reportCards->first(); @endphp
                <div class="flex items-center justify-between py-4 border-b border-neutral-100 dark:border-neutral-800 last:border-b-0 last:pb-0 first:pt-0">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $term->name ?? 'Term' }}</p>
                            @if($reportCard && $reportCard->isPublished())
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    Position: {{ $reportCard->position_in_class ?? 'N/A' }} of {{ $reportCard->total_students_in_class ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    Promotion: {{ ucfirst($reportCard->promotion_decision ?? 'pending') }}
                                </p>
                            @else
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Not available yet</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($reportCard && $reportCard->isPublished())
                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">
                                Published
                            </span>
                            <a href="{{ route('parent.children.report-cards.download', [$student->id, $reportCard->id]) }}"
                               class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200" aria-label="Download PDF">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 19V5m0 0L7 10m5-5l5 5"/></svg>
                            </a>
                        @else
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-2.5 py-0.5 text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                Unavailable
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No report cards yet.</p>
                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Report cards will appear here once published by the school.</p>
                </div>
            @endforelse
        </div>
    </x-ui.card>

    <div class="mt-4">
        <a href="{{ route('parent.children.show', $student->id) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
            ← Back to Child Overview
        </a>
    </div>
</x-layouts.app>
