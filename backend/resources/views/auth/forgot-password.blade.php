<x-layouts.guest>
    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border shadow-premium-lg p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Forgot Password</h2>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Enter the email address associated with your parent account and we'll send you a link to reset your password.</p>
                </div>

                @if(session('status'))
                    <x-ui.alert variant="success" class="mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
                        </div>
                    </x-ui.alert>
                @endif

                @if($errors->any())
                    <x-ui.alert variant="danger" class="mb-4">
                        <div class="flex flex-col gap-2">
                            <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">Please fix the following errors:</h4>
                            <ul class="list-disc list-inside text-sm text-danger-700 dark:text-danger-400 space-y-1">
                                <li>{{ $errors->first() }}</li>
                            </ul>
                        </div>
                    </x-ui.alert>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-neutral-300 dark:hover:border-neutral-600">
                        @error('email')
                            @if($message !== $errors->first())
                                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p>
                            @endif
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold px-6 py-3 rounded-xl shadow-premium hover:shadow-premium-lg transition-all duration-200 btn-shine">
                        Send Reset Link
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-neutral-600 dark:text-neutral-400">
                    Remember your password?
                    <a href="{{ route('login') }}" class="font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>
