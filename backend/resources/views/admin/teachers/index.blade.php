<x-layouts.app title="Teachers">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Teachers</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Teachers</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage teaching staff and their assignments.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Add Teacher</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Create a new teacher account with user credentials.</p>
                </div>
                <form method="POST" action="{{ route('admin.teachers.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Full Name <span class="text-danger-500">*</span></label>
                        <input id="name" name="name" type="text" required placeholder="e.g. Mrs. Smith" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email Address <span class="text-danger-500">*</span></label>
                        <input id="email" name="email" type="email" required placeholder="e.g. smith@school.edu" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Phone Number</label>
                        <input id="phone" name="phone" type="tel" placeholder="e.g. 08012345678" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="qualification" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Qualification</label>
                        <input id="qualification" name="qualification" type="text" placeholder="e.g. B.Ed Mathematics" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Add Teacher</button>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-7">
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Qualification</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @forelse($teachers as $teacher)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $teacher->user->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $teacher->user->email ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $teacher->qualification ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Active</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
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