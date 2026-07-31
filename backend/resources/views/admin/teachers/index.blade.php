<x-layouts.app title="Teachers">
    @php
        $breadcrumbs = [
            ['label' => 'Admin', 'href' => '/admin/dashboard'],
            ['label' => 'Teachers', 'active' => true],
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
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Teachers</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage teaching staff and their assignments.</p>
            </div>
        </div>
    </x-slot:title>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Add Teacher</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Register a new teacher.</p>
                </div>
                <form method="POST" action="{{ route('admin.teachers.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">User Account <span class="text-danger-500">*</span></label>
                        <select id="user_id" name="user_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none">
                            <option value="">Select a user</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->user->id }}">{{ $teacher->user->name }} ({{ $teacher->user->email }})</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Only users with teacher role are shown.</p>
                    </div>
                    <div>
                        <label for="specialization" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Specialization</label>
                        <input id="specialization" name="specialization" type="text" placeholder="e.g. Mathematics" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Add Teacher</button>
                </form>
            </x-ui.card>
        </div>
        <div class="lg:col-span-8">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">All Teachers</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $teachers->count() }} teacher{{ $teachers->count() !== 1 ? 's' : '' }} registered.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Specialization</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @forelse($teachers as $teacher)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $teacher->user->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $teacher->user->email ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $teacher->specialization ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Active</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">No teachers found.</p>
                                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Add your first teacher using the form.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
