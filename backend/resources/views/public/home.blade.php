<x-layouts.guest>
    {{-- ============================================ --}}
    {{-- 1. PREMIUM NAVIGATION (via navbar component) --}}
    {{-- 2. HERO SECTION (via hero component)         --}}
    {{-- ============================================ --}}
    <x-public.hero
        title="Where Young Minds Become Tomorrow's Leaders"
        subtitle="Greenfield Academy has nurtured excellence since 1995 — blending a rigorous Nigerian curriculum with personalised attention, world-class facilities, and a values-driven community."
        :primaryCta="['href' => '/admissions', 'label' => 'Apply Now']"
        :secondaryCta="['href' => '/about', 'label' => 'Discover Greenfield']"
    />

    {{-- ============================================ --}}
    {{-- 3. SCHOOL STATISTICS                         --}}
    {{-- ============================================ --}}
    <section class="bg-neutral-50 dark:bg-dark-bg border-y border-neutral-200 dark:border-dark-border">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 py-16 lg:py-20">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6">
                <div class="animate-on-scroll">
                    <x-public.stat-card label="Students Enrolled" value="500+" icon="users" description="Across all year groups" />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.1s;">
                    <x-public.stat-card label="Expert Teachers" value="25+" icon="graduation-cap" description="Qualified & dedicated" />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.2s;">
                    <x-public.stat-card label="Years of Excellence" value="29" icon="school" description="Established 1995" />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.3s;">
                    <x-public.stat-card label="Exam Pass Rate" value="100%" icon="award" description="JSS 3 Exit Exams 2026" />
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 4. WHY CHOOSE GREENFIELD ACADEMY             --}}
    {{-- ============================================ --}}
    <section class="bg-white dark:bg-dark-surface py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center mb-16 lg:mb-20">
                <div class="lg:col-span-7 animate-on-scroll">
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/40 px-3.5 py-1.5 text-xs font-semibold text-primary-700 dark:text-primary-300 mb-5">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                        Why Families Choose Greenfield
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-neutral-900 dark:text-white tracking-tight leading-[1.15] mb-5">
                        An education that goes beyond textbooks
                    </h2>
                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed max-w-xl">
                        We are a prestigious private school dedicated to nurturing young minds through academic excellence, creative thinking, and strong moral values. Every child has unique potential waiting to be unlocked.
                    </p>
                </div>
                <div class="lg:col-span-5 animate-on-scroll" style="transition-delay: 0.15s;">
                    <div class="relative rounded-3xl overflow-hidden shadow-premium-xl image-zoom aspect-4/3">
                        <img src="{{ asset('images/why_choose_us_img.webp') }}" alt="Students collaborating at Greenfield Academy" class="w-full h-full object-cover zoom-target" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6">
                <div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border p-7 shadow-premium card-lift hover:shadow-premium-lg animate-on-scroll">
                    <div class="h-12 w-12 rounded-xl bg-linear-to-br from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/20 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-110">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Academic Excellence</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">A comprehensive curriculum designed to foster critical thinking, creativity, and a lifelong love for learning.</p>
                </div>

                <div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border p-7 shadow-premium card-lift hover:shadow-premium-lg animate-on-scroll" style="transition-delay: 0.1s;">
                    <div class="h-12 w-12 rounded-xl bg-linear-to-br from-accent-50 to-accent-100 dark:from-accent-900/30 dark:to-accent-800/20 text-accent-600 dark:text-accent-400 flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-110">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Expert Faculty</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">Dedicated, qualified teachers committed to nurturing every student's individual potential and growth.</p>
                </div>

                <div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border p-7 shadow-premium card-lift hover:shadow-premium-lg animate-on-scroll" style="transition-delay: 0.2s;">
                    <div class="h-12 w-12 rounded-xl bg-linear-to-br from-success-50 to-success-100 dark:from-success-900/30 dark:to-success-800/20 text-success-600 dark:text-success-400 flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-110">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Holistic Development</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">Focus on academics, sports, arts, and character building to shape well-rounded, confident individuals.</p>
                </div>

                <div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border p-7 shadow-premium card-lift hover:shadow-premium-lg animate-on-scroll" style="transition-delay: 0.3s;">
                    <div class="h-12 w-12 rounded-xl bg-linear-to-br from-warning-50 to-warning-100 dark:from-warning-900/30 dark:to-warning-800/20 text-warning-600 dark:text-warning-400 flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-110">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10m-2 2h-4m4 0h4"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Safe Environment</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">A secure, supportive campus where students feel valued, respected, and inspired to learn every day.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 5. ACADEMIC PROGRAMMES                       --}}
    {{-- ============================================ --}}
    <section class="bg-neutral-50 dark:bg-dark-bg py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <x-public.section-header
                title="Academic Programmes"
                subtitle="Our curriculum is designed to challenge, inspire, and prepare students for future success at every stage of their journey."
                badge="Our Curriculum"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium overflow-hidden card-lift hover:shadow-premium-lg animate-on-scroll">
                    <div class="aspect-video overflow-hidden relative">
                        <img src="{{ asset('images/Jss1_img.webp') }}" alt="JSS 1 foundation year" class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                        <div class="absolute inset-0 bg-linear-to-t from-black/50 to-transparent"></div>
                        <span class="absolute top-4 left-4 inline-flex items-center rounded-full bg-white/90 dark:bg-dark-surface/90 backdrop-blur-sm px-3 py-1 text-xs font-bold text-neutral-900 dark:text-white shadow-sm">Year 1</span>
                    </div>
                    <div class="p-7">
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">JSS 1 — Foundation</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed mb-5">The foundation year builds strong fundamentals in Mathematics, English, Basic Science, and introductory subjects that spark curiosity.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Mathematics</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">English</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Basic Science</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Social Studies</span>
                        </div>
                    </div>
                </div>

                <div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium overflow-hidden card-lift hover:shadow-premium-lg animate-on-scroll" style="transition-delay: 0.1s;">
                    <div class="aspect-video overflow-hidden relative">
                        <img src="{{ asset('images/Jss2_img.webp') }}" alt="JSS 2 intermediate year" class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                        <div class="absolute inset-0 bg-linear-to-t from-black/50 to-transparent"></div>
                        <span class="absolute top-4 left-4 inline-flex items-center rounded-full bg-white/90 dark:bg-dark-surface/90 backdrop-blur-sm px-3 py-1 text-xs font-bold text-neutral-900 dark:text-white shadow-sm">Year 2</span>
                    </div>
                    <div class="p-7">
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">JSS 2 — Intermediate</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed mb-5">The intermediate year deepens subject knowledge and introduces more complex concepts across all disciplines and electives.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Mathematics</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">English</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Science</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Tech & Arts</span>
                        </div>
                    </div>
                </div>

                <div class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium overflow-hidden card-lift hover:shadow-premium-lg animate-on-scroll" style="transition-delay: 0.2s;">
                    <div class="aspect-video overflow-hidden relative">
                        <img src="{{ asset('images/Jss3_img.webp') }}" alt="JSS 3 examination year" class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                        <div class="absolute inset-0 bg-linear-to-t from-black/50 to-transparent"></div>
                        <span class="absolute top-4 left-4 inline-flex items-center rounded-full bg-white/90 dark:bg-dark-surface/90 backdrop-blur-sm px-3 py-1 text-xs font-bold text-neutral-900 dark:text-white shadow-sm">Year 3</span>
                    </div>
                    <div class="p-7">
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">JSS 3 — Examination</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed mb-5">The final JSS year focuses on examination preparation, advanced topics, and a smooth transition to senior secondary education.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Exam Prep</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Advanced Topics</span>
                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Career Guidance</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12 animate-on-scroll">
                <a href="/academics" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700 px-6 py-3 text-sm shadow-premium">
                    Explore Full Curriculum
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 6. CAMPUS FACILITIES                         --}}
    {{-- ============================================ --}}
    <section class="bg-white dark:bg-dark-surface py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <x-public.section-header
                title="Campus Facilities"
                subtitle="State-of-the-art resources designed to support every aspect of learning, discovery, and personal development."
                badge="Our Campus"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="animate-on-scroll">
                    <x-public.facility-card title="Modern Classrooms" description="Spacious, well-lit classrooms equipped with interactive whiteboards and comfortable seating for focused learning." icon="book-open" image="images/facilities_img1.webp" />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.1s;">
                    <x-public.facility-card title="Science Laboratory" description="Fully equipped labs for hands-on experiments in physics, chemistry, and biology with safety-first design." icon="flask-conical" />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.2s;">
                    <x-public.facility-card title="Computer Lab" description="Modern computing facilities with high-speed internet and the latest educational software for digital learning." icon="cpu" />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.3s;">
                    <x-public.facility-card title="Library & Resource Center" description="An extensive collection of books, journals, and digital resources supporting research and a reading culture." icon="book-open" image="images/library_img.webp" />
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 7. STUDENT LIFE                              --}}
    {{-- ============================================ --}}
    <section class="bg-neutral-50 dark:bg-dark-bg py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center mb-16">
                <div class="lg:col-span-6 animate-on-scroll">
                    <div class="relative">
                        <div class="rounded-3xl overflow-hidden shadow-premium-xl image-zoom aspect-4/3">
                            <img src="{{ asset('images/student_life_img.webp') }}" alt="Students at assembly" class="w-full h-full object-cover zoom-target" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('bg-linear-to-br','from-neutral-100','to-neutral-200','dark:from-neutral-800','dark:to-neutral-700')">
                        </div>
                        <div class="absolute -bottom-6 -right-6 bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border shadow-premium-xl p-5 hidden sm:block">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-xl bg-linear-to-br from-accent-400 to-accent-600 flex items-center justify-center shadow-lg">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-neutral-900 dark:text-white leading-none">20+</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Clubs & Activities</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-6 animate-on-scroll" style="transition-delay: 0.15s;">
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/40 px-3.5 py-1.5 text-xs font-semibold text-primary-700 dark:text-primary-300 mb-5">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                        Student Life
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-neutral-900 dark:text-white tracking-tight leading-[1.15] mb-5">
                        A vibrant community where learning extends beyond the classroom
                    </h2>
                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed mb-8">
                        From sports and music to debate club and science fairs, Greenfield Academy offers a rich tapestry of extracurricular activities. Students discover their passions, build lasting friendships, and develop the confidence to lead.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <span class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Sports & Athletics</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="h-5 w-5 text-accent-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                            </div>
                            <span class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Music & Arts</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            </div>
                            <span class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Science Club</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="h-5 w-5 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.047L3 20.5M14.5 11l5 5M21 21l-3.5-3.5M14.5 11L9 5.5"/></svg>
                            </div>
                            <span class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Debate Society</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 8. NEWS & EVENTS                             --}}
    {{-- ============================================ --}}
    <section class="bg-white dark:bg-dark-surface py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 mb-12 lg:mb-16 animate-on-scroll">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/40 px-3.5 py-1.5 text-xs font-semibold text-primary-700 dark:text-primary-300 mb-5">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                        News & Events
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-neutral-900 dark:text-white tracking-tight leading-[1.15] mb-4">
                        Stay connected with our community
                    </h2>
                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        The latest happenings, achievements, and upcoming events at Greenfield Academy.
                    </p>
                </div>
                <a href="/news" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 px-5 py-2.5 text-sm shadow-premium shrink-0">
                    View All News
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="animate-on-scroll">
                        <x-public.news-card
                            title="Welcome Back to School"
                            excerpt="We are excited to welcome all our students back for the 2026/2027 academic session. School resumes on September 1st with orientation activities planned throughout the week."
                            date="Aug 28, 2026"
                            author="Admin"
                            image="school"
                            category="Announcement"
                            href="/news"
                        />
                    </div>
                    <div class="animate-on-scroll" style="transition-delay: 0.1s;">
                        <x-public.news-card
                            title="Science Fair Winners Announced"
                            excerpt="Congratulations to all participants in this year's Science Fair. The winning projects showcased incredible innovation and scientific reasoning from our JSS students."
                            date="Aug 20, 2026"
                            author="Mrs. Smith"
                            image="science"
                            category="Achievement"
                            href="/news"
                        />
                    </div>
                </div>

                <div class="space-y-4 animate-on-scroll" style="transition-delay: 0.2s;">
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider mb-2 px-1">Upcoming Events</h3>
                    <x-public.event-card
                        title="New Student Orientation"
                        date="2026-09-01"
                        time="09:00 AM"
                        location="Main Hall"
                        description="Welcome session for all new students joining Greenfield Academy this term."
                        href="/news"
                    />
                    <x-public.event-card
                        title="Inter-House Sports"
                        date="2026-09-15"
                        time="08:00 AM"
                        location="School Field"
                        description="Annual inter-house sports competition featuring track and field events."
                        href="/news"
                    />
                    <x-public.event-card
                        title="Parent-Teacher Conference"
                        date="2026-10-05"
                        time="10:00 AM"
                        location="Classrooms"
                        description="Discuss student progress and academic performance with class teachers."
                        href="/news"
                    />
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 9. TESTIMONIALS                              --}}
    {{-- ============================================ --}}
    <section class="bg-neutral-50 dark:bg-dark-bg py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <x-public.section-header
                title="What Our Community Says"
                subtitle="Hear from the families and students who make Greenfield Academy a place of belonging and growth."
                badge="Testimonials"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="animate-on-scroll">
                    <x-public.testimonial
                        quote="Greenfield Academy has been a blessing for our family. The teachers genuinely care about each student's progress and well-being, and we have seen remarkable growth in our son."
                        author="Mr. and Mrs. Doe"
                        role="Parents, JSS 1"
                        avatar="D"
                    />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.1s;">
                    <x-public.testimonial
                        quote="The curriculum is challenging yet engaging. My daughter has grown so much academically and socially since joining. The small class sizes make a real difference."
                        author="Mr. and Mrs. Smith"
                        role="Parents, JSS 2"
                        avatar="S"
                    />
                </div>
                <div class="animate-on-scroll" style="transition-delay: 0.2s;">
                    <x-public.testimonial
                        quote="I love my teachers and friends here. The school feels like a second home, and I look forward to coming every day. Science club is my favourite!"
                        author="Jane Smith"
                        role="Student, JSS 2"
                        avatar="J"
                    />
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 10. GALLERY PREVIEW                          --}}
    {{-- ============================================ --}}
    <section class="bg-white dark:bg-dark-surface py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 mb-12 lg:mb-16 animate-on-scroll">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/40 px-3.5 py-1.5 text-xs font-semibold text-primary-700 dark:text-primary-300 mb-5">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                        Campus Gallery
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-neutral-900 dark:text-white tracking-tight leading-[1.15] mb-4">
                        A glimpse into life at Greenfield
                    </h2>
                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Moments of discovery, creativity, and joy captured across our campus every day.
                    </p>
                </div>
                <a href="/gallery" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-white dark:bg-dark-surface border border-neutral-200 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 px-5 py-2.5 text-sm shadow-premium shrink-0">
                    Full Gallery
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                <div class="animate-on-scroll"><x-public.gallery-item title="Classroom Learning" category="Academics" /></div>
                <div class="animate-on-scroll" style="transition-delay: 0.05s;"><x-public.gallery-item title="Sports Day" category="Athletics" /></div>
                <div class="animate-on-scroll" style="transition-delay: 0.1s;"><x-public.gallery-item title="Science Lab" category="STEM" /></div>
                <div class="animate-on-scroll" style="transition-delay: 0.15s;"><x-public.gallery-item title="Art Class" category="Creative Arts" /></div>
                <div class="animate-on-scroll" style="transition-delay: 0.05s;"><x-public.gallery-item title="Library" category="Resources" /></div>
                <div class="animate-on-scroll" style="transition-delay: 0.1s;"><x-public.gallery-item title="Music Room" category="Performing Arts" /></div>
                <div class="animate-on-scroll" style="transition-delay: 0.15s;"><x-public.gallery-item title="Assembly" category="Community" /></div>
                <div class="animate-on-scroll" style="transition-delay: 0.2s;"><x-public.gallery-item title="Field Trip" category="Experiential" /></div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 11. ADMISSION CALL-TO-ACTION                 --}}
    {{-- ============================================ --}}
    <section class="bg-neutral-50 dark:bg-dark-bg py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <div class="animate-on-scroll">
                <x-public.cta-banner
                    title="Ready to Join Greenfield Academy?"
                    subtitle="Applications for the 2026/2027 academic session are now open. Take the first step toward an exceptional education for your child."
                    :primaryCta="['href' => '/admissions', 'label' => 'Apply Now']"
                    :secondaryCta="['href' => '/contact', 'label' => 'Schedule a Visit']"
                />
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 12. CONTACT SECTION                          --}}
    {{-- ============================================ --}}
    <section class="bg-white dark:bg-dark-surface py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
            <x-public.section-header
                title="Get in Touch"
                subtitle="We'd love to hear from you. Contact us for admissions, inquiries, or to schedule a campus tour."
                badge="Contact Us"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="mailto:info@greenfieldacademy.edu" class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border p-8 shadow-premium card-lift hover:shadow-premium-lg text-center animate-on-scroll">
                    <div class="h-14 w-14 rounded-2xl bg-linear-to-br from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/20 text-primary-600 dark:text-primary-400 flex items-center justify-center mx-auto mb-5 transition-transform duration-300 group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-2">Email Us</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">For admissions & general inquiries</p>
                    <p class="text-sm font-semibold text-primary-600 dark:text-primary-400 group-hover:underline">info@greenfieldacademy.edu</p>
                </a>

                <a href="tel:+2348000000000" class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border p-8 shadow-premium card-lift hover:shadow-premium-lg text-center animate-on-scroll" style="transition-delay: 0.1s;">
                    <div class="h-14 w-14 rounded-2xl bg-linear-to-br from-accent-50 to-accent-100 dark:from-accent-900/30 dark:to-accent-800/20 text-accent-600 dark:text-accent-400 flex items-center justify-center mx-auto mb-5 transition-transform duration-300 group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-2">Call Us</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Monday to Friday, 8am to 4pm</p>
                    <p class="text-sm font-semibold text-primary-600 dark:text-primary-400 group-hover:underline">+234 800 000 0000</p>
                </a>

                <a href="/contact" class="group bg-white dark:bg-dark-surface rounded-2xl border border-neutral-200 dark:border-dark-border p-8 shadow-premium card-lift hover:shadow-premium-lg text-center animate-on-scroll" style="transition-delay: 0.2s;">
                    <div class="h-14 w-14 rounded-2xl bg-linear-to-br from-success-50 to-success-100 dark:from-success-900/30 dark:to-success-800/20 text-success-600 dark:text-success-400 flex items-center justify-center mx-auto mb-5 transition-transform duration-300 group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-2">Visit Us</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Schedule a campus tour</p>
                    <p class="text-sm font-semibold text-primary-600 dark:text-primary-400 group-hover:underline">{{ config('school.address', '123 Education Lane, Victoria Island, Lagos') }}</p>
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 13. PREMIUM FOOTER (via footer component)    --}}
    {{-- ============================================ --}}
</x-layouts.guest>
