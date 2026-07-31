@props([
    'schoolName' => 'Greenfield Academy',
    'schoolDescription' => 'Nurturing young minds through academic excellence, creative thinking, and strong moral values since 1995.',
    'address' => '123 Education Lane, Greenfield City, State 10001',
    'phone' => '+234 800 000 0000',
    'email' => 'info@greenfieldacademy.edu',
])

@php
    $iconPaths = [
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'phone' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        'map-pin' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        'facebook' => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z',
        'twitter' => 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z',
        'instagram' => 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z M17.5 6.5h.01 M2 12a10 10 0 0110-10h0a10 10 0 0110 10v0a10 10 0 01-10 10h0a10 10 0 01-10-10z',
        'linkedin' => 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z M4 9h4v12H4z M2 7h4v4H2z',
        'youtube' => 'M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19.1C5.12 19.56 12 19.56 12 19.56s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.33z M9.75 15.02l5.75-3.27-5.75-3.27v6.54z',
    ];
@endphp

<footer class="bg-neutral-50 dark:bg-dark-surface border-t border-neutral-200 dark:border-dark-border transition-colors duration-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-8 w-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-sm">GA</div>
                    <span class="text-lg font-bold text-neutral-900 dark:text-white">{{ $schoolName }}</span>
                </div>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">{{ $schoolDescription }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white uppercase tracking-wider mb-4">Quick Links</h3>
                <ul class="space-y-2.5">
                    <li><a href="/" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Home</a></li>
                    <li><a href="/about" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">About Us</a></li>
                    <li><a href="/admissions" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Admissions</a></li>
                    <li><a href="/academics" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Academics</a></li>
                    <li><a href="/news" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">News</a></li>
                    <li><a href="/contact" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white uppercase tracking-wider mb-4">Contact Info</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2.5">
                        <svg class="h-5 w-5 text-neutral-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['map-pin'] }}"/></svg>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $address }}</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="h-5 w-5 text-neutral-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['phone'] }}"/></svg>
                        <a href="tel:{{ $phone }}" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ $phone }}</a>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="h-5 w-5 text-neutral-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['mail'] }}"/></svg>
                        <a href="mailto:{{ $email }}" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ $email }}</a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white uppercase tracking-wider mb-4">Connect With Us</h3>
                <div class="flex items-center gap-3 mb-6">
                    <a href="#" class="h-9 w-9 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 dark:text-neutral-400 hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" aria-label="Facebook">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['facebook'] }}"/></svg>
                    </a>
                    <a href="#" class="h-9 w-9 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 dark:text-neutral-400 hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" aria-label="Twitter">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['twitter'] }}"/></svg>
                    </a>
                    <a href="#" class="h-9 w-9 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 dark:text-neutral-400 hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" aria-label="Instagram">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['instagram'] }}"/></svg>
                    </a>
                    <a href="#" class="h-9 w-9 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 dark:text-neutral-400 hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" aria-label="LinkedIn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths['linkedin'] }}"/></svg>
                    </a>
                </div>

                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white uppercase tracking-wider mb-3">Newsletter</h3>
                <form class="flex gap-2" onsubmit="event.preventDefault();">
                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input id="newsletter-email" type="email" placeholder="Your email" class="flex-1 min-w-0 rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <button type="submit" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-2 shadow-sm shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-neutral-200 dark:border-dark-border flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">&copy; {{ date('Y') }} {{ $schoolName }}. All rights reserved.</p>
            <div class="flex items-center gap-6">
                <a href="/privacy" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Privacy Policy</a>
                <a href="/terms" class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
