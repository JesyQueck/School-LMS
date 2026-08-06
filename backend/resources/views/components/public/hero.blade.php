@props([
    'title' => 'Welcome to Greenfield Academy',
    'subtitle' => 'Empowering students to achieve excellence through a nurturing environment, dedicated faculty, and a world-class curriculum.',
    'primaryCta' => ['href' => '/admissions', 'label' => 'Apply Now'],
    'secondaryCta' => ['href' => '/about', 'label' => 'Learn More'],
    'image' => null,
])

<section class="relative overflow-hidden bg-white dark:bg-dark-bg">
    <div class="absolute inset-0 bg-linear-to-b from-primary-50/40 via-white to-white dark:from-primary-900/5 dark:via-dark-bg dark:to-dark-bg"></div>

    <div class="relative max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 pt-16 pb-20 lg:pt-24 lg:pb-32">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-10 items-center">
            <div class="lg:col-span-6 max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-white dark:bg-dark-surface border border-primary-200 dark:border-primary-800/50 px-4 py-1.5 mb-7 shadow-premium animate-fade-in-up">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-600"></span>
                    </span>
                    <span class="text-xs font-semibold text-primary-700 dark:text-primary-300 tracking-wide">2026/2027 Admissions Now Open</span>
                </div>

                <h1 class="text-[2.5rem] sm:text-5xl lg:text-[3.75rem] font-bold text-neutral-900 dark:text-white leading-[1.05] tracking-tight mb-6 animate-fade-in-up delay-100">
                    {{ $title }}
                </h1>

                <p class="text-lg sm:text-xl text-neutral-600 dark:text-neutral-400 leading-relaxed mb-9 max-w-xl animate-fade-in-up delay-200">
                    {{ $subtitle }}
                </p>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-10 animate-fade-in-up delay-300">
                    <a href="{{ $primaryCta['href'] }}" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-primary-600 hover:bg-primary-700 text-white shadow-premium-lg hover:shadow-premium-xl hover:-translate-y-0.5 px-7 py-3.5 text-base btn-shine">
                        {{ $primaryCta['label'] }}
                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ $secondaryCta['href'] }}" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700 px-7 py-3.5 text-base">
                        {{ $secondaryCta['label'] }}
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-x-8 gap-y-3 animate-fade-in-up delay-400">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Accredited Curriculum</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">29 Years of Excellence</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6 relative animate-scale-in delay-200">
                <div class="relative grid grid-cols-2 gap-4">
                    <div class="col-span-2 relative rounded-3xl overflow-hidden shadow-premium-xl image-zoom aspect-16/10">
                        <img src="{{ asset('images/Hero_img1.png') }}" alt="Greenfield Academy modern campus building" class="w-full h-full object-cover zoom-target" loading="eager" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                        <div class="absolute inset-0 bg-linear-to-t from-black/20 to-transparent"></div>
                    </div>

                    <div class="relative rounded-2xl overflow-hidden shadow-premium-lg image-zoom aspect-square">
                        <img src="{{ asset('images/Hero_img2.png') }}" alt="Students in classroom at Greenfield Academy" class="w-full h-full object-cover zoom-target" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                    </div>
                    <div class="relative rounded-2xl overflow-hidden shadow-premium-lg image-zoom aspect-square">
                        <img src="{{ asset('images/Hero_img3.png') }}" alt="Academic excellence at Greenfield Academy" class="w-full h-full object-cover zoom-target" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                    </div>
                </div>

                <div class="absolute -bottom-6 -left-6 bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium-xl p-4 lg:p-5 hidden sm:block animate-float">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-linear-to-br from-success-400 to-success-600 flex items-center justify-center shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-neutral-900 dark:text-white leading-none">100%</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pass Rate - 2026</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -top-4 -right-4 bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium-lg p-3 lg:p-4 hidden sm:block">
                    <div class="flex items-center gap-2.5">
                        <div class="h-9 w-9 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-neutral-900 dark:text-white leading-none">Top 5%</p>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">National Ranking</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
