<x-layouts.guest>
    <section class="bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 mb-4">Contact Us</span>
                <h1 class="text-4xl sm:text-5xl font-bold text-neutral-900 dark:text-white tracking-tight mb-6">Get in Touch</h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">We would love to hear from you. Reach out for admissions inquiries, general questions, or to schedule a campus visit.</p>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm p-6 lg:p-8">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-6">Send Us a Message</h2>
                        <form class="space-y-5" onsubmit="event.preventDefault();">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="contact-first-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">First Name</label>
                                    <input id="contact-first-name" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="John">
                                </div>
                                <div>
                                    <label for="contact-last-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Last Name</label>
                                    <input id="contact-last-name" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Doe">
                                </div>
                            </div>
                            <div>
                                <label for="contact-email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
                                <input id="contact-email" type="email" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="you@example.com">
                            </div>
                            <div>
                                <label for="contact-subject" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Subject</label>
                                <select id="contact-subject" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none">
                                    <option value="">Select a topic</option>
                                    <option value="admissions">Admissions Inquiry</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="visit">Campus Visit</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="contact-message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Message</label>
                                <textarea id="contact-message" rows="5" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-vertical" placeholder="How can we help you?"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Send Message</button>
                        </form>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm p-6">
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white mb-4">Contact Information</h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">Email</p>
                                    <a href="mailto:info@greenfieldacademy.edu" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">info@greenfieldacademy.edu</a>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center shrink-0">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">Phone</p>
                                    <a href="tel:+2348000000000" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">+234 800 000 0000</a>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center shrink-0">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">Address</p>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ config('school.address', '123 Education Lane, Victoria Island, Lagos') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm p-6">
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white mb-4">Office Hours</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-500 dark:text-neutral-400">Monday - Friday</span>
                                <span class="text-neutral-900 dark:text-white font-medium">8:00 AM - 4:00 PM</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-500 dark:text-neutral-400">Saturday</span>
                                <span class="text-neutral-900 dark:text-white font-medium">9:00 AM - 1:00 PM</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-500 dark:text-neutral-400">Sunday</span>
                                <span class="text-neutral-900 dark:text-white font-medium">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white dark:bg-dark-surface py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden aspect-[16/7] bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                <div class="text-center">
                    <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Interactive map placeholder — integrate with Google Maps or Mapbox</p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.guest>
