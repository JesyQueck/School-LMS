<x-layouts.app title="Parent Dashboard">
    @php
        $breadcrumbs = [
            ['label' => 'Parent', 'href' => '/parent/dashboard', 'active' => true],
        ];
    @endphp

    <x-slot:title>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <x-ui.breadcrumbs>
                    @foreach($breadcrumbs as $crumb)
                        <x-ui.breadcrumb-item :href="$crumb['href'] ?? null" :active="$crumb['active'] ?? false">
                            {{ $crumb['label'] }}
                        </x-ui.breadcrumb-item>
                    @endforeach
                </x-ui.breadcrumbs>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Parent Dashboard</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Monitor your child's academic progress and school activities.</p>
            </div>
        </div>
    </x-slot:title>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Children" :value="$children->count()" :trend="['direction' => 'neutral', 'value' => 'Enrolled']" icon="users" />
        <x-ui.stat-card label="Attendance" value="98%" :trend="['direction' => 'up', 'value' => 'This term']" icon="calendar-check" />
        <x-ui.stat-card label="Average Grade" value="A-" :trend="['direction' => 'up', 'value' => 'From B+ last term']" icon="clipboard-list" />
        <x-ui.stat-card label="Announcements" :value="$announcements->count()" :trend="['direction' => 'neutral', 'value' => 'For parents']" icon="megaphone" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">My Children</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">View detailed information for each child.</p>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($children as $child)
                        <a href="{{ route('parent.children.show', $child) }}" class="flex items-center gap-4 p-4 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors group mb-3 last:mb-0">
                            <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium text-lg flex-shrink-0">
                                {{ substr($child->full_name ?? $child->admission_no, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ $child->full_name ?? 'Unknown' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $child->class->name ?? 'Unassigned' }} &middot; {{ $child->admission_no ?? 'N/A' }}</p>
                            </div>
                            <svg class="h-5 w-5 text-neutral-400 group-hover:text-primary-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">No children linked to your account.</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Contact the school administrator to link students.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Latest Announcements</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Updates relevant to parents.</p>
                </div>
                <div class="p-6">
                    @forelse($announcements as $announcement)
                        <div class="py-3 border-b border-neutral-100 dark:border-neutral-800 last:border-b-0">
                            <h4 class="text-sm font-medium text-neutral-900 dark:text-white">{{ $announcement->title }}</h4>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 line-clamp-2">{{ $announcement->body }}</p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">By {{ $announcement->createdBy->name ?? 'Admin' }} &middot; {{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No announcements at this time.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
