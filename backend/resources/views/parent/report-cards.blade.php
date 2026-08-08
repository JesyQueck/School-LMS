<x-layouts.app title="Report Cards">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('parent.dashboard') }}">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('parent.children.show', $student) }}">Children</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Report Cards</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
    </div>

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $student->full_name ?? 'Student' }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Published report cards for {{ $student->admission_no ?? '' }}</p>
    </div>

    <x-ui.card>
        <div class="p-6">
            @forelse($publishedReportCards as $reportCard)
                <div class="flex items-center justify-between p-4 rounded-lg border border-neutral-200 dark:border-dark-border mb-3 last:mb-0">
                    <div>
                        <h3 class="text-sm font-medium text-neutral-900 dark:text-white">{{ $reportCard->term->name ?? 'Term' }} Report Card</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $reportCard->term->session ?? '' }} Session</p>
                    </div>
                    <a href="{{ route('admin.report-cards.download', $reportCard) }}" class="text-xs px-3 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        Download PDF
                    </a>
                </div>
            @empty
                <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-8">No published report cards available.</p>
            @endforelse
        </div>
    </x-ui.card>
</x-layouts.app>