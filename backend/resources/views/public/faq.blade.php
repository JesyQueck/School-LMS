<x-layouts.guest>
    <section class="bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 mb-4">FAQ</span>
                <h1 class="text-4xl sm:text-5xl font-bold text-neutral-900 dark:text-white tracking-tight mb-6">Frequently Asked Questions</h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">Find answers to common questions about admissions, academics, and life at Greenfield Academy.</p>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-4" x-data="{ active: null }">
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                    <button @click="active = active === 1 ? null : 1" class="w-full flex items-center justify-between p-5 text-left" aria-expanded="false" x-bind:aria-expanded="(active === 1).toString()">
                        <span class="text-base font-semibold text-neutral-900 dark:text-white">What are the school hours?</span>
                        <svg class="h-5 w-5 text-neutral-400 shrink-0 transition-transform" :class="active === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="active === 1" x-collapse class="px-5 pb-5">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">School hours are Monday through Friday, 8:00 AM to 3:30 PM. Extra-curricular activities and clubs run until 5:00 PM. We also offer Saturday enrichment classes from 9:00 AM to 1:00 PM.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                    <button @click="active = active === 2 ? null : 2" class="w-full flex items-center justify-between p-5 text-left" aria-expanded="false" x-bind:aria-expanded="(active === 2).toString()">
                        <span class="text-base font-semibold text-neutral-900 dark:text-white">What is the student-to-teacher ratio?</span>
                        <svg class="h-5 w-5 text-neutral-400 shrink-0 transition-transform" :class="active === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="active === 2" x-collapse class="px-5 pb-5">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">We maintain a low student-to-teacher ratio of approximately 20:1 to ensure personalized attention. This allows our teachers to identify and support each student's unique learning needs.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                    <button @click="active = active === 3 ? null : 3" class="w-full flex items-center justify-between p-5 text-left" aria-expanded="false" x-bind:aria-expanded="(active === 3).toString()">
                        <span class="text-base font-semibold text-neutral-900 dark:text-white">Do you offer transportation services?</span>
                        <svg class="h-5 w-5 text-neutral-400 shrink-0 transition-transform" :class="active === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="active === 3" x-collapse class="px-5 pb-5">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">Yes, we provide safe and reliable school bus transportation covering major routes within the city. Bus routes and schedules are arranged during the enrollment process based on student location.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                    <button @click="active = active === 4 ? null : 4" class="w-full flex items-center justify-between p-5 text-left" aria-expanded="false" x-bind:aria-expanded="(active === 4).toString()">
                        <span class="text-base font-semibold text-neutral-900 dark:text-white">What extracurricular activities are available?</span>
                        <svg class="h-5 w-5 text-neutral-400 shrink-0 transition-transform" :class="active === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="active === 4" x-collapse class="px-5 pb-5">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">We offer a wide range of activities including football, basketball, athletics, music, drama, art, debate club, science club, and chess. Students are encouraged to explore their interests beyond the classroom.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                    <button @click="active = active === 5 ? null : 5" class="w-full flex items-center justify-between p-5 text-left" aria-expanded="false" x-bind:aria-expanded="(active === 5).toString()">
                        <span class="text-base font-semibold text-neutral-900 dark:text-white">When does the academic year begin?</span>
                        <svg class="h-5 w-5 text-neutral-400 shrink-0 transition-transform" :class="active === 5 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="active === 5" x-collapse class="px-5 pb-5">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">The academic year typically begins in early September and runs through July, divided into three terms. Exact dates are published each year and communicated to parents in advance.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                    <button @click="active = active === 6 ? null : 6" class="w-full flex items-center justify-between p-5 text-left" aria-expanded="false" x-bind:aria-expanded="(active === 6).toString()">
                        <span class="text-base font-semibold text-neutral-900 dark:text-white">Is there a school uniform?</span>
                        <svg class="h-5 w-5 text-neutral-400 shrink-0 transition-transform" :class="active === 6 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="active === 6" x-collapse class="px-5 pb-5">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">Yes, students are required to wear the Greenfield Academy uniform. Details on uniform requirements and where to purchase them are provided during the enrollment process.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                    <button @click="active = active === 7 ? null : 7" class="w-full flex items-center justify-between p-5 text-left" aria-expanded="false" x-bind:aria-expanded="(active === 7).toString()">
                        <span class="text-base font-semibold text-neutral-900 dark:text-white">Are there scholarship opportunities?</span>
                        <svg class="h-5 w-5 text-neutral-400 shrink-0 transition-transform" :class="active === 7 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="active === 7" x-collapse class="px-5 pb-5">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">Yes, we offer merit-based and need-based scholarships for eligible students. Please contact our admissions office for details on eligibility criteria and the application process.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-public.cta-banner
        title="Still Have Questions?"
        subtitle="Our admissions team is here to help. Reach out and we will get back to you promptly."
        :primaryCta="['href' => '/contact', 'label' => 'Contact Us']"
        :secondaryCta="['href' => '/admissions', 'label' => 'Apply Now']"
        background="primary"
    />
</x-layouts.guest>
