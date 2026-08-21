<x-layouts.app title="Accounts">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Accounts</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-2">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">All Accounts</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                    @php
                        $counts = $users->groupBy('role')->map->count();
                    @endphp
                    Manage user accounts.
                    @foreach($roles as $r)
                        <span class="mx-1 opacity-50">|</span>
                        {{ ucfirst($r) }}: <strong>{{ $counts[$r] ?? 0 }}</strong>
                    @endforeach
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.accounts.create') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Create Account
                </a>
                <a href="{{ route('admin.accounts.credentials') }}" class="inline-flex items-center gap-2 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.202c.019.77.38 1.503 1.066 2.04l3.584 2.87a2 2 0 001.397.54h1.343a2 2 0 001.99-2.21l-.333-2.221a.75.75 0 01.47-1.33l2.04-1.225a2 2 0 00.47-1.15v-2.222a2 2 0 00-.47-1.15l-2.04-1.225a.75.75 0 01-.47-1.33l.333-2.221A2 2 0 0018.25 8.417v-1.16a2 2 0 00-.894-1.664l-3.221-1.866a2 2 0 00-1.69-.227l-.585.164M9 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Credentials
                </a>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mt-4">
            <a href="{{ route('admin.accounts.index') }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors @if(!$currentRole) bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700 @else bg-neutral-50 dark:bg-dark-surface text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800 @endif">
                All
            </a>
            @foreach($roles as $role)
                @php
                    $colors = [
                        'teacher' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-300 dark:border-indigo-700',
                        'student' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-300 dark:border-green-700',
                        'parent' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border-purple-300 dark:border-purple-700',
                        'admin' => 'bg-neutral-100 dark:bg-neutral-900/30 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700',
                    ];
                @endphp
                <a href="{{ route('admin.accounts.index', ['role' => $role]) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors @if($currentRole === $role) {{ $colors[$role] }} @else bg-neutral-50 dark:bg-dark-surface text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800 @endif">
                    {{ ucfirst($role) }}
                </a>
            @endforeach
        </div>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($users as $user)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                    @class([
                                        'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' => $user->role === 'admin',
                                        'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' => $user->role === 'teacher',
                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' => $user->role === 'student',
                                        'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' => $user->role === 'parent',
                                    ])>
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full @if($user->is_active) bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300" else="bg-neutral-100 dark:bg-neutral-800/30 text-neutral-700 dark:text-neutral-300 @endif">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 01-8 0 4 4 0 008 0z"/></svg>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No accounts found.</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Create your first account using the button above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
