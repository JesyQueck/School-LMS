<x-layouts.guest>
    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border shadow-premium-lg p-8">
                <div class="text-center mb-8">
                    <div class="mx-auto mb-6">
                        <img src="{{ asset('images/Logo.webp') }}" alt="{{ config('school.name', 'Greenfield Academy') }}" class="h-24 w-auto object-contain">
                    </div>
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Sign in to your account</h2>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Welcome back to {{ config('school.name', config('app.name', 'Greenfield Academy')) }}</p>
                </div>

                @if(session('status'))
                    <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
                @endif

                @if($errors->any())
                    <x-ui.alert variant="danger" class="mb-4">{{ $errors->first() }}</x-ui.alert>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-4 py-2.5 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-neutral-300 dark:hover:border-neutral-600">
                        @error('email')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border-2 border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-4 py-2.5 pr-12 text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-neutral-300 dark:hover:border-neutral-600">
                            <button type="button" id="toggle-password" class="absolute top-0 bottom-0 right-0 flex items-center px-3 text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors" aria-label="Toggle password visibility">
                                <svg id="eye-open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
                                </svg>
                                <svg id="eye-slash" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 110-6 3 3 0 010 6z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4l16 16"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-1"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p>
                        @enderror
                    </div>

                    <script>
                        (function() {
                            var btn = document.getElementById('toggle-password');
                            var pwd = document.getElementById('password');
                            var eyeOpen = document.getElementById('eye-open');
                            var eyeSlash = document.getElementById('eye-slash');

                            btn.addEventListener('click', function() {
                                if (pwd.type === 'password') {
                                    pwd.type = 'text';
                                    eyeOpen.style.display = 'none';
                                    eyeSlash.style.display = '';
                                } else {
                                    pwd.type = 'password';
                                    eyeOpen.style.display = '';
                                    eyeSlash.style.display = 'none';
                                }
                            });
                        })();
                    </script>

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
