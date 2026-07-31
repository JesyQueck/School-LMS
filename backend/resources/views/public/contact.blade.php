<x-layouts.guest>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-neutral-900 dark:text-white mb-4">Contact Us</h1>
            <p class="text-lg text-neutral-600 dark:text-neutral-400">We'd love to hear from you. Get in touch with us.</p>
        </div>

        <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Get in Touch</h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-neutral-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Email</p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">info@school.edu</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-neutral-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Phone</p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">+234 800 000 0000</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-neutral-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Address</p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">123 Education Lane, City, State</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Send a Message</h2>
                    <form class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                            <input id="name" name="name" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
                            <input id="email" name="email" type="email" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Message</label>
                            <textarea id="message" name="message" rows="4" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-vertical"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
