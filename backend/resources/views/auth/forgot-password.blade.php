<x-layouts.guest title="Forgot Password">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto h-12 w-12 rounded-xl bg-primary-600 flex items-center justify-center mb-4">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">Forgot Password</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Enter your email address and we'll send you a link to reset your password.</p>
            </div>

            <x-ui.card>
                <form method="POST" action="{{ route('password.email') }}" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Email address <span class="text-danger-500">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3.5 py-2.5 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        @error('email')
                            <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Send Reset Link</button>
                </form>
                <div class="px-6 py-4 border-t border-neutral-200 dark:border-dark-border bg-neutral-50 dark:bg-neutral-800/30 rounded-b-xl">
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
                        Remember your password? <a href="{{ route('login') }}" class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">Sign in</a>
                    </p>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.guest>
