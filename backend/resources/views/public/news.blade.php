<x-layouts.guest>
    <section class="bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 mb-4">News</span>
                <h1 class="text-4xl sm:text-5xl font-bold text-neutral-900 dark:text-white tracking-tight mb-6">Latest News</h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">Stay updated with the latest news, stories, and announcements from Greenfield Academy.</p>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-public.news-card
                    title="Welcome Back to School"
                    excerpt="We are excited to welcome all our students back for the 2026/2027 academic session. School resumes on September 1st with an exciting lineup of activities."
                    date="August 28, 2026"
                    author="Admin"
                    href="#"
                />
                <x-public.news-card
                    title="PTA Meeting This Saturday"
                    excerpt="All parents are cordially invited to attend our quarterly Parent-Teacher Association meeting this Saturday at 10am in the main hall."
                    date="August 25, 2026"
                    author="Admin"
                    href="#"
                />
                <x-public.news-card
                    title="Science Fair Winners Announced"
                    excerpt="Congratulations to all participants in this year's Science Fair. The winning projects showcased incredible innovation and scientific thinking."
                    date="August 20, 2026"
                    author="Mrs. Smith"
                    href="#"
                />
                <x-public.news-card
                    title="New Computer Lab Opening"
                    excerpt="We are thrilled to announce the opening of our state-of-the-art computer laboratory equipped with 40 modern workstations."
                    date="August 15, 2026"
                    author="Mr. Jones"
                    href="#"
                />
                <x-public.news-card
                    title="Inter-House Sports Results"
                    excerpt="Green House emerged as the overall winner of this year's inter-house sports competition. Congratulations to all participants."
                    date="August 10, 2026"
                    author="Admin"
                    href="#"
                />
                <x-public.news-card
                    title="Scholarship Opportunities"
                    excerpt="Applications are now open for the Greenfield Academy Merit Scholarship for outstanding students entering JSS 1."
                    date="August 5, 2026"
                    author="Admin"
                    href="#"
                />
            </div>
        </div>
    </section>
</x-layouts.guest>
