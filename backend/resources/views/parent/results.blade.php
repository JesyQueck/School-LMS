<x-layouts.app title="Results">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/parent/dashboard">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('parent.children.show', $student) }}">{{ $student->full_name ?? 'Child' }}</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Results</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Results</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $student->full_name ?? 'Student' }} &middot; {{ $student->schoolClass->name ?? 'N/A' }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($results->groupBy('term.name') as $termName => $termResults)
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $termName }}</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $termResults->count() }} subject{{ $termResults->count() !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Subject</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">CA Score</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Exam Score</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @foreach($termResults as $result)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $result->classSubject->subject->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $result->ca_score ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $result->exam_score ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $result->total ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">{{ $result->grade ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="No results available" description="There are no published results for this student yet. Results will appear here once published by the school." />
        @endforelse
    </div>
</x-layouts.app>
