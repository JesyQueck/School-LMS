@props([
    'title' => 'Welcome to Greenfield Academy',
    'subtitle' => 'Empowering students to achieve excellence through a nurturing environment, dedicated faculty, and a world-class curriculum.',
    'primaryCta' => ['href' => '/admissions', 'label' => 'Apply Now'],
    'secondaryCta' => ['href' => '/about', 'label' => 'Learn More'],
    'image' => null,
])

<section class="relative bg-white dark:bg-dark-surface overflow-hidden pt-6 sm:pt-8">
    <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-accent-50 dark:from-primary-900/10 dark:via-dark-surface dark:to-accent-900/10"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 mb-6">
                    <span class="h-2 w-2 rounded-full bg-primary-600 dark:bg-primary-400 animate-pulse"></span>
                    <span class="text-xs font-medium text-primary-700 dark:text-primary-300">2026/2027 Admissions Open</span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-neutral-900 dark:text-white leading-tight tracking-tight mb-6">
                    {{ $title }}
                </h1>
                <p class="text-lg sm:text-xl text-neutral-600 dark:text-neutral-400 leading-relaxed mb-8 max-w-xl">
                    {{ $subtitle }}
                </p>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <a href="{{ $primaryCta['href'] }}" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-primary-600 hover:bg-primary-700 text-white shadow-sm px-6 py-3 text-base">
                        {{ $primaryCta['label'] }}
                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ $secondaryCta['href'] }}" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 px-6 py-3 text-base">
                        {{ $secondaryCta['label'] }}
                    </a>
                </div>
            </div>

            <div class="relative hidden lg:block">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                    <div class="aspect-[4/3] bg-gradient-to-br from-primary-100 to-accent-100 dark:from-primary-900/30 dark:to-accent-900/30 rounded-2xl flex items-center justify-center">
                        <div class="text-center p-8">
                            <div class="h-24 w-24 rounded-full bg-primary-200 dark:bg-primary-800/50 flex items-center justify-center mx-auto mb-4">
                                <svg class="h-12 w-12 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7"/></svg>
                            </div>
                            <p class="text-lg font-semibold text-primary-800 dark:text-primary-200">Excellence in Education</p>
                            <p class="text-sm text-primary-600 dark:text-primary-300 mt-1">Established 1995</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-4 -left-4 bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                            <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white">100% Pass Rate</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">JSS 3 Exit Exams 2026</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
