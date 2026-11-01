<x-layouts.app title="My Children">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">My Children</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $children->count() }} child{{ $children->count() !== 1 ? 'ren' : '' }} linked to your account</p>
            </div>
        </div>
    </div>

    @if($children->isEmpty())
        <x-ui.empty-state
            title="No children linked"
            description="Contact the school administrator to link students to your account."
            :action="['label' => 'Contact Support', 'href' => route('parent.dashboard')]"
        />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($children as $child)
                <x-ui.card hoverable class="p-4">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            @if($child->user && $child->user->profile_photo)
                                <img src="{{ asset('storage/' . $child->user->profile_photo) }}" alt="{{ $child->full_name }}" class="h-16 w-16 rounded-full object-cover">
                            @else
                                <div class="h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-2xl flex-shrink-0">
                                    {{ substr($child->full_name ?? $child->admission_no, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate">{{ $child->full_name ?? 'Unknown' }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ $child->schoolClass->name ?? 'Unassigned' }}</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500">Adm: {{ $child->admission_no ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-1.5 text-center">
                        <div class="p-2 rounded bg-neutral-50 dark:bg-neutral-800/50">
                            <p class="text-[10px] text-neutral-500 dark:text-neutral-400">Avg</p>
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $child->metrics['average_score'] }}%</p>
                        </div>
                        <div class="p-2 rounded bg-neutral-50 dark:bg-neutral-800/50">
                            <p class="text-[10px] text-neutral-500 dark:text-neutral-400">Att</p>
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $child->metrics['attendance_rate'] }}%</p>
                        </div>
                        <div class="p-2 rounded bg-neutral-50 dark:bg-neutral-800/50">
                            <p class="text-[10px] text-neutral-500 dark:text-neutral-400">Fees</p>
                            <p class="text-xs font-semibold {{ $child->metrics['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                {{ $child->metrics['outstanding'] > 0 ? '₦' . number_format($child->metrics['outstanding'], 0) : 'Paid' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('parent.children.show', $child) }}" class="flex-1 text-center text-xs font-medium px-3 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">View Profile</a>
                        <a href="{{ route('parent.children.results', $child) }}" class="flex-1 text-center text-xs font-medium px-3 py-2 rounded-lg border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">View Results</a>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.app>