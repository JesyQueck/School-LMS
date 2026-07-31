<x-layouts.guest>
    <section class="bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 mb-4">Gallery</span>
                <h1 class="text-4xl sm:text-5xl font-bold text-neutral-900 dark:text-white tracking-tight mb-6">Campus Life</h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">A glimpse into the vibrant and dynamic community at Greenfield Academy.</p>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
                <button class="inline-flex items-center rounded-full bg-primary-600 text-white px-4 py-2 text-sm font-medium shadow-sm">All</button>
                <button class="inline-flex items-center rounded-full bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-neutral-300 px-4 py-2 text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Academics</button>
                <button class="inline-flex items-center rounded-full bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-neutral-300 px-4 py-2 text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Athletics</button>
                <button class="inline-flex items-center rounded-full bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-neutral-300 px-4 py-2 text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Arts</button>
                <button class="inline-flex items-center rounded-full bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-neutral-300 px-4 py-2 text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Events</button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <x-public.gallery-item title="Classroom Learning" category="Academics" />
                <x-public.gallery-item title="Science Lab" category="Academics" />
                <x-public.gallery-item title="Library Reading" category="Academics" />
                <x-public.gallery-item title="Sports Day" category="Athletics" />
                <x-public.gallery-item title="Football Match" category="Athletics" />
                <x-public.gallery-item title="Athletics Track" category="Athletics" />
                <x-public.gallery-item title="Art Exhibition" category="Arts" />
                <x-public.gallery-item title="Music Performance" category="Arts" />
                <x-public.gallery-item title="Drama Club" category="Arts" />
                <x-public.gallery-item title="Graduation Day" category="Events" />
                <x-public.gallery-item title="Prize Giving" category="Events" />
                <x-public.gallery-item title="Cultural Day" category="Events" />
                <x-public.gallery-item title="Field Trip" category="Academics" />
                <x-public.gallery-item title="Science Fair" category="Academics" />
                <x-public.gallery-item title="Swimming" category="Athletics" />
                <x-public.gallery-item title="Talent Show" category="Events" />
            </div>
        </div>
    </section>
</x-layouts.guest>
