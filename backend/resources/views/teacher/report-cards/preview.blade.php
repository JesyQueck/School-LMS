<x-layouts.app title="Report Card Preview">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/teacher/dashboard">Teacher</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/teacher/report-cards">Report Cards</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Preview</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>

        <div class="flex items-center justify-between mt-2">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">
                    {{ $reportCard->term->name ?? 'Report Card' }} Preview
                </h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                    This is exactly what your downloaded PDF will look like.
                </p>
            </div>

            <a href="{{ route('teacher.report-cards.download', $reportCard) }}"
               class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>
        </div>
    </div>

    {{--
        Why an iframe: report-card markup uses its own dompdf-safe table-based
        CSS (see pdf/report-card.blade.php). Rendering that HTML directly on
        this page would collide with the app's Tailwind classes and dark
        mode. The iframe gives it an isolated document context, and because
        the src route below renders the exact same Blade file that gets
        passed to Dompdf, what the teacher sees here IS what downloads.
    --}}
    <div class="rounded-lg border border-neutral-200 dark:border-dark-border overflow-hidden bg-neutral-100 dark:bg-neutral-900" style="height: 85vh;">
        <iframe
            src="{{ route('teacher.report-cards.render', $reportCard) }}"
            title="Report card preview"
            class="w-full h-full border-0"
            loading="lazy"
        ></iframe>
    </div>

    <div class="mt-4">
        <a href="{{ route('teacher.report-cards.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
            ← Back to Report Cards
        </a>
    </div>
</x-layouts.app>
