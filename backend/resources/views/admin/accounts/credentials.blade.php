<x-layouts.app title="Credentials">
    <x-ui.page-header title="Account Credentials" subtitle="Manage student and teacher temporary passwords." back-href="{{ route('admin.accounts.index') }}" back-label="Back to accounts">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumbs>
                <x-ui.breadcrumb-item href="{{ route('admin.dashboard') }}">Admin</x-ui.breadcrumb-item>
                <x-ui.breadcrumb-item href="{{ route('admin.accounts.index') }}">Accounts</x-ui.breadcrumb-item>
                <x-ui.breadcrumb-item active>Credentials</x-ui.breadcrumb-item>
            </x-ui.breadcrumbs>
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <a href="{{ route('admin.accounts.credentials.download', request()->query()) }}" class="inline-flex items-center gap-2 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                Download CSV
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif
    <div id="credentials-feedback" class="mb-4" role="status" aria-live="polite"></div>

    @php
        $roleColors = [
            'admin' => 'bg-neutral-100 dark:bg-neutral-900/30 text-neutral-700 dark:text-neutral-300',
            'teacher' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
            'student' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
            'parent' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
        ];
    @endphp

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.accounts.credentials') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 p-5">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Search accounts</label>
                <input id="search" name="search" type="search" value="{{ $search }}" maxlength="100"
                    placeholder="Name, admission no., Staff ID, email, parent ID"
                    class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface px-3 py-2 text-sm">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Role</label>
                <select id="role" name="role" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface px-3 py-2 text-sm">
                    <option value="">All roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected($currentRole === $role)>{{ $roleLabels[$role] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="password_status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Password status</label>
                <select id="password_status" name="password_status" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface px-3 py-2 text-sm">
                    <option value="">All statuses</option>
                    <option value="required" @selected($passwordStatus === 'required')>Password Change Required</option>
                    <option value="changed" @selected($passwordStatus === 'changed')>Password Changed</option>
                </select>
            </div>
            <div class="md:col-span-4 flex items-center gap-3">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg text-sm">Search and filter</button>
                <a href="{{ route('admin.accounts.credentials') }}" class="text-sm text-neutral-600 dark:text-neutral-300 underline">Clear filters</a>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card>
        <div class="px-5 py-3 border-b border-neutral-200 dark:border-dark-border text-sm text-neutral-600 dark:text-neutral-300">
            {{ number_format($credentials->total()) }} account{{ $credentials->total() === 1 ? '' : 's' }} found
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-[1500px] w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="min-w-[180px] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Account</th>
                        <th class="min-w-[130px] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Role</th>
                        <th class="min-w-[180px] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Login identifier</th>
                        <th class="min-w-[220px] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                        <th class="w-[340px] min-w-[340px] max-w-[340px] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Temporary Password</th>
                        <th class="min-w-[250px] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Password status</th>
                        <th class="min-w-[260px] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($credentials as $user)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $user->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $roleColors[$user->role] ?? '' }}">
                                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-neutral-600 dark:text-neutral-300">{{ $user->loginIdentifier() ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-600 dark:text-neutral-300">{{ $user->email ?: '—' }}</td>
                            <td class="w-[340px] min-w-[340px] max-w-[340px] whitespace-nowrap px-6 py-4 text-sm" data-temporary-password-cell>
                                @if(isset($temporaryPasswords[$user->id]))
                                    <div class="flex items-center gap-3">
                                        <span data-temporary-password class="font-mono font-semibold text-neutral-900 dark:text-white">{{ $temporaryPasswords[$user->id] }}</span>
                                        <button type="button" data-copy-temporary-password class="shrink-0 rounded-md border border-neutral-300 dark:border-dark-border px-3 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-neutral-800 focus-visible:ring-2 focus-visible:ring-primary-500">
                                            Copy
                                        </button>
                                        <span data-copy-feedback class="min-w-[60px] text-xs text-neutral-500 dark:text-neutral-400" aria-live="polite"></span>
                                    </div>
                                @else
                                    <span class="text-neutral-500 dark:text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm" data-password-status-cell>
                                @if($user->needs_password_change)
                                    <span data-password-status class="inline-flex min-w-[210px] shrink-0 items-center justify-center whitespace-nowrap rounded-full bg-warning-100 px-4 py-2 text-center text-xs font-medium leading-5 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300">Password Change Required</span>
                                @else
                                    <span data-password-status class="inline-flex min-w-[210px] shrink-0 items-center justify-center whitespace-nowrap rounded-full bg-success-100 px-4 py-2 text-center text-xs font-medium leading-5 text-success-700 dark:bg-success-900/30 dark:text-success-300">Password Changed</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @if(in_array($user->role, ['student', 'teacher'], true))
                                    <div class="flex items-center gap-4">
                                        <form method="POST" action="{{ route('admin.accounts.credentials.password.regenerate', $user) }}" data-regenerate-password-form>
                                            @csrf
                                            <button type="submit" class="text-warning-700 dark:text-warning-400 hover:underline font-medium" data-regenerate-password-button>Generate New Password</button>
                                        </form>
                                    </div>
                                @elseif($user->role === 'parent')
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Use email password recovery</span>
                                @else
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Not available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-neutral-500">No accounts match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($credentials->hasPages())
            <div class="px-5 py-4 border-t border-neutral-200 dark:border-dark-border">
                {{ $credentials->links() }}
            </div>
        @endif
    </x-ui.card>
    <script>
        const credentialsFeedback = document.getElementById('credentials-feedback');

        function showCredentialsFeedback(message, failed = false) {
            credentialsFeedback.textContent = message;
            credentialsFeedback.className = failed
                ? 'mb-4 rounded-lg bg-danger-50 px-4 py-3 text-sm text-danger-800 dark:bg-danger-950/40 dark:text-danger-300'
                : 'mb-4 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-800 dark:bg-success-950/40 dark:text-success-300';
        }

        function renderTemporaryPassword(cell, password) {
            cell.replaceChildren();
            const container = document.createElement('div');
            container.className = 'flex items-center gap-3';
            const passwordText = document.createElement('span');
            passwordText.dataset.temporaryPassword = '';
            passwordText.className = 'font-mono font-semibold text-neutral-900 dark:text-white';
            passwordText.textContent = password;
            const copyButton = document.createElement('button');
            copyButton.type = 'button';
            copyButton.dataset.copyTemporaryPassword = '';
            copyButton.className = 'shrink-0 rounded-md border border-neutral-300 dark:border-dark-border px-3 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-neutral-800 focus-visible:ring-2 focus-visible:ring-primary-500';
            copyButton.textContent = 'Copy';
            const copyFeedback = document.createElement('span');
            copyFeedback.dataset.copyFeedback = '';
            copyFeedback.className = 'min-w-[60px] text-xs text-neutral-500 dark:text-neutral-400';
            copyFeedback.setAttribute('aria-live', 'polite');
            container.append(passwordText, copyButton, copyFeedback);
            cell.append(container);
        }

        document.addEventListener('click', async (event) => {
            const copyButton = event.target.closest('[data-copy-temporary-password]');
            if (!copyButton) return;

            const container = copyButton.parentElement;
            const password = container.querySelector('[data-temporary-password]').textContent;
            const feedback = container.querySelector('[data-copy-feedback]');
            let copied = false;

            try {
                if (!navigator.clipboard || !navigator.clipboard.writeText) {
                    throw new Error('Clipboard API unavailable');
                }
                await navigator.clipboard.writeText(password);
                copied = true;
            } catch {
                const input = document.createElement('textarea');
                input.value = password;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                try {
                    input.select();
                    copied = document.execCommand('copy');
                } catch {
                    copied = false;
                } finally {
                    input.remove();
                }
            }

            feedback.textContent = copied ? 'Copied' : 'Copy failed';
            window.setTimeout(() => {
                feedback.textContent = '';
            }, 2000);
        });

        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('[data-regenerate-password-form]');
            if (!form) return;

            event.preventDefault();
            const button = form.querySelector('[data-regenerate-password-button]');
            if (button.disabled) return;

            const originalLabel = button.textContent;
            button.disabled = true;
            button.textContent = 'Generating...';
            button.classList.add('cursor-wait', 'opacity-60');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    },
                    body: new FormData(form),
                });
                let result;
                try {
                    result = await response.json();
                } catch {
                    throw new Error('Unable to generate a new password. Please refresh and try again.');
                }
                if (!response.ok) {
                    throw new Error(result.message || 'Unable to generate a new password.');
                }
                if (typeof result.password !== 'string' || !result.password) {
                    throw new Error('The server did not return a valid temporary password.');
                }

                const row = form.closest('tr');
                renderTemporaryPassword(row.querySelector('[data-temporary-password-cell]'), result.password);
                const status = row.querySelector('[data-password-status]');
                status.textContent = result.password_status;
                status.className = 'inline-flex min-w-[210px] shrink-0 items-center justify-center whitespace-nowrap rounded-full bg-warning-100 px-4 py-2 text-center text-xs font-medium leading-5 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300';
                showCredentialsFeedback(result.message);
            } catch (error) {
                showCredentialsFeedback(error.message || 'Unable to generate a new password. Please try again.', true);
            } finally {
                button.disabled = false;
                button.textContent = originalLabel;
                button.classList.remove('cursor-wait', 'opacity-60');
            }
        });
    </script>
</x-layouts.app>
