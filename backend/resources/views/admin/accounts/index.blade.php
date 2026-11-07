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
                    Manage user accounts.
                    {{ $users->total() }} total accounts.
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

        @if(session('status'))
            <x-ui.alert variant="success" class="mb-6">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
                </div>
            </x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert variant="danger" class="mb-6">
                <div class="flex flex-col gap-2">
                    <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside text-sm text-danger-700 dark:text-danger-400 space-y-1">
                        <li>{{ $errors->first() }}</li>
                    </ul>
                </div>
            </x-ui.alert>
        @endif

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
                        'bursar' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-700',
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
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
                                        'bg-neutral-100 dark:bg-neutral-900/30 text-neutral-700 dark:text-neutral-300' => $user->role === 'bursar',
                                    ])>
                                        {{ $user->role }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    @class([
                                        'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' => $user->is_active,
                                        'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300' => ! $user->is_active,
                                    ])>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <!-- Deactivate: active, non-admin, non-self -->
                                @if($user->is_active && $user->role !== 'admin' && $user->id !== auth()->id())
                                    <button type="button"
                                        onclick="openAccountModal('deactivate', {{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes(route('admin.accounts.destroy', $user)) }}')"
                                        class="inline-flex items-center rounded-lg border border-warning-400 bg-warning-100 px-3 py-1.5 text-xs font-medium text-neutral-800 hover:bg-warning-200 dark:text-neutral-100 dark:border-warning-600 dark:bg-warning-900 dark:hover:bg-warning-800 transition-colors">
                                        Deactivate
                                    </button>
                                @endif

                                <!-- Permanently Delete: inactive bursar, non-admin, non-self -->
                                @if(!$user->is_active && $user->role === 'bursar' && $user->id !== auth()->id())
                                    <button type="button"
                                        onclick="openAccountModal('delete', {{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes(route('admin.accounts.permanent-delete', $user)) }}')"
                                        class="ml-2 inline-flex items-center rounded-lg border border-danger-300 bg-danger-50 px-3 py-1.5 text-xs font-medium text-danger-700 hover:bg-danger-100 dark:border-danger-700 dark:bg-danger-900/20 dark:text-danger-300 dark:hover:bg-danger-800/30 transition-colors">
                                        Permanently Delete
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No accounts found.</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Create your first account using the button above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-neutral-200 dark:border-dark-border">
                {{ $users->links() }}
            </div>
        @endif
    </x-ui.card>

    <!-- Shared confirmation modal (vanilla JS) -->
    <div id="account-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAccountModal()"></div>
        <div class="relative bg-white dark:bg-dark-surface rounded-2xl shadow-premium-xl border-2 border-neutral-200 dark:border-dark-border w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border flex items-center justify-between">
                <h3 id="modal-title" class="text-xl font-bold text-neutral-900 dark:text-white"></h3>
                <button onclick="closeAccountModal()" class="p-2 rounded-xl text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all duration-200" aria-label="Close modal">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <p id="modal-body" class="text-sm text-neutral-600 dark:text-neutral-400"></p>
            </div>
            <div class="px-6 py-4 border-t-2 border-neutral-200 dark:border-dark-border flex items-center justify-end gap-3">
                <button type="button" onclick="closeAccountModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-neutral-600 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800 transition-colors">
                    Cancel
                </button>
                <form id="account-action-form" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button id="modal-confirm-btn" type="button" onclick="confirmAccountAction()" class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-colors" style="min-width: 140px;">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAccountModal(type, userId, userName, url) {
            const modal = document.getElementById('account-modal');
            const title = document.getElementById('modal-title');
            const body = document.getElementById('modal-body');
            const form = document.getElementById('account-action-form');
            const btn = document.getElementById('modal-confirm-btn');

            form.action = url;

            if (type === 'delete') {
                title.textContent = 'Permanently Delete Account?';
                body.textContent = 'This action cannot be undone. The account "' + userName + '" will be permanently removed.';
                btn.textContent = 'Permanently Delete';
                btn.className = 'px-4 py-2 rounded-lg text-sm font-medium text-danger-800 bg-danger-100 hover:bg-danger-200 border border-danger-300 transition-colors';
            } else {
                title.textContent = 'Deactivate Account?';
                body.textContent = 'This user will no longer be able to sign in. Their account and associated records will be preserved.';
                btn.textContent = 'Deactivate Account';
                btn.className = 'px-4 py-2 rounded-lg text-sm font-medium text-warning-800 bg-warning-100 hover:bg-warning-200 border border-warning-400 transition-colors';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeAccountModal() {
            const modal = document.getElementById('account-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function confirmAccountAction() {
            const title = document.getElementById('modal-title').textContent;
            const form = document.getElementById('account-action-form');
            form.submit();
        }
    </script>
</x-layouts.app>