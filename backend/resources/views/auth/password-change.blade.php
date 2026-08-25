<x-layouts.guest title="Change Password">
    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border shadow-premium-lg p-8">
                <div class="mx-auto mb-6">
                    <img src="{{ asset('images/Logo.webp') }}" alt="{{ config('school.name', 'Greenfield Academy') }}" class="h-24 w-auto object-contain">
                </div>
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mt-4">Change Password</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Set your new password to complete your account setup.</p>
                </div>

                @if(session('status'))
                    <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
                @endif

                <form method="POST" action="{{ route('password.change.update') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Current Password</label>
                        <input id="current_password" name="current_password" type="password" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('current_password')<p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">New Password</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('password')<p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-400 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('password_confirmation')<p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold px-6 py-3 rounded-xl shadow-premium hover:shadow-premium-lg transition-all duration-200 btn-shine">
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>