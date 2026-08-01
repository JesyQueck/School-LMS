<x-layouts.guest>
    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm p-8">
                <div class="text-center mb-8">
                    <div class="mx-auto h-12 w-12 rounded-lg bg-primary-600 flex items-center justify-center text-white text-xl font-bold mb-4">S</div>
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Create your account</h2>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Already have an account? <a href="{{ route('login') }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">Sign in</a></p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        @error('name')<p class="mt-2 text-sm text-danger-600 dark:text-danger-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        @error('email')<p class="mt-2 text-sm text-danger-600 dark:text-danger-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Phone number</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        @error('phone')<p class="mt-2 text-sm text-danger-600 dark:text-danger-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Password</label>
                        <input id="password" name="password" type="password" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        @error('password')<p class="mt-2 text-sm text-danger-600 dark:text-danger-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">
                        Create account
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
