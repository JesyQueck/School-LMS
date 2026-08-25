<x-layouts.guest title="Forgot Password">
    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="mx-auto mb-6">
                <img src="{{ asset('images/Logo.webp') }}" alt="{{ config('school.name', 'Greenfield Academy') }}" class="h-24 w-auto object-contain">
            </div>
                <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">Forgot Password</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Enter your email address and we'll send you a link to reset your password.</p>
            </div>

            <div class="bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border shadow-premium-lg">
                <form method="POST" action="{{ route('password.email') }}" class="p-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Email address <span class="text-danger-500">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com" class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-neutral-300 dark:hover:border-neutral-600">
                        @error('email')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold px-6 py-3 rounded-xl shadow-premium hover:shadow-premium-lg transition-all duration-200 btn-shine">Send Reset Link</button>
                </form>
                <div class="px-8 py-5 border-t-2 border-neutral-200 dark:border-dark-border bg-neutral-50 dark:bg-neutral-800/30 rounded-b-2xl">
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
                        Remember your password? <a href="{{ route('login') }}" class="font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
