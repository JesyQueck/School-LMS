<x-layouts.app title="Timetable">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/parent/dashboard">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Timetable</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">My Children's Timetable</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Select a child to view their weekly class schedule.</p>
    </div>

    @if($children && $children->count() > 0)
        <div class="mb-6 max-w-xs">
            <label for="child-select" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Child</label>
            <select id="child-select" class="w-full rounded-lg border border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface px-3 py-2 text-sm text-neutral-900 dark:text-white" onchange="window.location.href = '?student=' + this.value">
                @foreach($children as $child)
                    <option value="{{ $child->id }}" {{ $selected && $selected->id === $child->id ? 'selected' : '' }}>
                        {{ $child->full_name ?? $child->admission_no }} - {{ $child->schoolClass->name ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($selected)
            <div class="mb-4">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Showing timetable for <span class="font-medium text-neutral-900 dark:text-white">{{ $selected->full_name }}</span>
                    &middot; {{ $selected->schoolClass->name ?? 'N/A' }}
                </p>
            </div>
        @endif

        @if($periods->count() > 0)
            <div class="space-y-4">
                @foreach($days as $day)
                    @php $dayClasses = $periods->where('day', $day)->sortBy('period'); @endphp
                    <x-ui.card>
                        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $day }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Period</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Subject</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Teacher</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-dark-surface">
                                        @forelse($dayClasses as $p)
                                            <tr class="border-b border-neutral-100 dark:border-dark-border last:border-0">
                                                <td class="px-4 py-3 text-sm text-neutral-900 dark:text-white">{{ $p['period'] }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">{{ $p['subject'] }}</td>
                                                <td class="px-4 py-3 text-sm text-neutral-500 dark:text-neutral-400">{{ $p['teacher'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-4 py-4 text-sm text-neutral-500 dark:text-neutral-400 text-center">No classes scheduled.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        @else
            <x-ui.empty-state title="No timetable available" description="There are no subjects scheduled for the selected child's class yet." />
        @endif
    @else
        <x-ui.empty-state title="No children linked" description="Contact the school administrator to link students to your account." />
    @endif
</x-layouts.app>
