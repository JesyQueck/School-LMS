<x-layouts.guest>
    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border shadow-premium-lg p-8">
                <div class="text-center mb-8">
                    <div class="mx-auto h-14 w-14 rounded-xl bg-linear-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-xl font-bold shadow-lg mb-4">GA</div>
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Sign in to your account</h2>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Welcome back to Greenfield Academy</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-neutral-300 dark:hover:border-neutral-600">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-neutral-300 dark:hover:border-neutral-600">
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input id="remember" name="remember" type="checkbox" class="h-5 w-5 rounded-lg border-2 border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-dark-surface dark:focus:ring-offset-dark-bg">
                            <label for="remember" class="text-sm font-medium text-neutral-700 dark:text-neutral-300 cursor-pointer">Remember me</label>
                        </div>
                        <a href="/forgot-password" class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">Forgot password?</a>
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold px-6 py-3 rounded-xl shadow-premium hover:shadow-premium-lg transition-all duration-200 btn-shine">
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
