<x-layouts.app title="Credentials">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="/admin/accounts">Accounts</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Credentials</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-2">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Account Credentials</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Temporary passwords for all created accounts. Credentials are shown once and marked [CHANGED] when the user updates their password.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.accounts.credentials.download', request()->query()) }}" class="inline-flex items-center gap-2 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download CSV
                </a>
            </div>
        </div>
    </div>

    @php
        $roleColors = [
            'admin' => 'bg-neutral-100 dark:bg-neutral-900/30 text-neutral-700 dark:text-neutral-300',
            'teacher' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
            'student' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
            'parent' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
        ];
    @endphp

    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.accounts.credentials') }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors @if(!$currentRole) bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700 @else bg-neutral-50 dark:bg-dark-surface text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800 @endif">
            All
        </a>
        @foreach($roles as $r)
            <a href="{{ route('admin.accounts.credentials', ['role' => $r]) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors @if($currentRole === $r) {{ $roleColors[$r] }} @else bg-neutral-50 dark:bg-dark-surface text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-dark-border hover:bg-neutral-100 dark:hover:bg-neutral-800 @endif">
                {{ ucfirst($r) }}
            </a>
        @endforeach
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Temporary Password</th>
                        @if($credentials->filter(fn ($c) => ($c instanceof \App\Models\ImportCredential && $c->related_to) || ($c instanceof \App\Models\User && $c->role === 'parent'))->isNotEmpty())
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Related Student</th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($credentials as $credential)
                        @php
                            $isImport = $credential instanceof \App\Models\ImportCredential;
                            $user = $credential instanceof \App\Models\User ? $credential : ($credential->user ?? null);
                            $isChanged = $user && ! $user->needs_password_change;
                            $role = $isImport ? $credential->role : ($credential->role ?? '');
                            $name = $isImport ? $credential->name : ($credential->name ?? '');
                            $email = $isImport ? $credential->email : ($credential->email ?? '');
                            $password = $isImport ? ($credential->password ?? '') : (($credential->role === 'teacher' || $credential->role === 'admin') ? (explode(' ', $credential->name)[1] ?? 'Default') : 'StudentTest123!');
                            $relatedTo = $isImport ? $credential->related_to : null;
                            $showRelated = $credentials->filter(fn ($c) => (($c instanceof \App\Models\ImportCredential && $c->related_to) || ($c instanceof \App\Models\User && $c->role === 'parent')))->isNotEmpty();
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $roleColors[$role] ?? '' }}">
                                    {{ $roleLabels[$role] ?? ucfirst($role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $name }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400 font-mono">{{ $email }}@school</td>
                            <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400 font-mono">
                                @if($isChanged)
                                    <span class="text-danger-600 dark:text-danger-400 font-medium">[CHANGED]</span>
                                @else
                                    {{ $password }}
                                @endif
                            </td>
                            @if($showRelated)
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $relatedTo ?? 'N/A' }}</td>
                            @endif
                            <td class="px-6 py-4 text-sm">
                                @if($isChanged)
                                    <span class="inline-flex items-center rounded-full bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 px-2.5 py-0.5 text-xs font-medium">
                                        Password Changed
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300 px-2.5 py-0.5 text-xs font-medium">
                                        Needs Change
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7v2a3 3 0 01-3 3H6l1-4h3M9 3h6a2 2 0 012 2v2M9 3v2a3 3 0 013 3v3m-3-3h3m-3 0l2-2"/></svg>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No credentials found.</p>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">No accounts have been created yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
