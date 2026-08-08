<x-layouts.app title="Class Performance">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">Class Performance</h2>
        <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">View class performance analytics</p>
    </div>

    @if($classAssignment && $students->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">
                        Class: {{ $classAssignment->class->name ?? 'N/A' }}
                        @if($classAssignment->term)
                            ({{ $classAssignment->term->name }})
                        @endif
                    </h3>
                </div>
                <div class="p-6">
                    <table class="w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Avg Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Position</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-dark-surface divide-y divide-neutral-200 dark:divide-neutral-800">
                            @foreach($students as $student)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">{{ $student->name ?? $student->full_name ?? $student->admission_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">--</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">#{{ $loop->iteration }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Class Rankings</h3>
                </div>
                <div class="p-4">
                    <p class="text-center text-neutral-500 dark:text-neutral-400">Performance chart coming soon...</p>
                </div>
            </x-ui.card>
        </div>
    </div>
    @else
    <x-ui.card>
        <div class="p-6 text-center">
            <p class="text-neutral-500 dark:text-neutral-400">No data available for this class.</p>
        </div>
    </x-ui.card>
    @endif
</x-layouts.app>