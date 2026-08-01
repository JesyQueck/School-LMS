@props([
    'title' => 'Ready to Join Our Community?',
    'subtitle' => 'Applications for the 2026/2027 academic session are now open. Schedule a campus visit or apply online today.',
    'primaryCta' => ['href' => '/admissions', 'label' => 'Apply Now'],
    'secondaryCta' => ['href' => '/contact', 'label' => 'Contact Us'],
    'background' => 'primary',
])

<section class="relative overflow-hidden rounded-3xl animated-gradient shadow-premium-xl">
    <div class="absolute inset-0 bg-linear-to-br from-primary-600/95 via-primary-700/95 to-accent-700/90"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-accent-400/20 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>

    <div class="relative max-w-7xl mx-auto px-8 sm:px-12 lg:px-16 py-16 lg:py-24">
        <div class="max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur-sm px-4 py-1.5 text-xs font-semibold text-white mb-6">
                <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                Admissions Open · 2026/2027
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-5 tracking-tight leading-tight">{{ $title }}</h2>
            <p class="text-lg text-white/85 mb-9 leading-relaxed max-w-2xl mx-auto">{{ $subtitle }}</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ $primaryCta['href'] }}" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-white text-primary-700 hover:bg-neutral-100 px-7 py-3.5 text-base shadow-lg hover:shadow-xl hover:-translate-y-0.5 btn-shine">
                    {{ $primaryCta['label'] }}
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ $secondaryCta['href'] }}" class="inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 bg-transparent border-2 border-white/30 text-white hover:bg-white/10 hover:border-white/50 px-7 py-3.5 text-base">
                    {{ $secondaryCta['label'] }}
                </a>
            </div>
        </div>
    </div>
</section>
