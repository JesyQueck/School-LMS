@props([
    'title' => 'Ready to Join Our Community?',
    'subtitle' => 'Applications for the 2026/2027 academic session are now open. Schedule a campus visit or apply online today.',
    'primaryCta' => ['href' => '/admissions', 'label' => 'Apply Now'],
    'secondaryCta' => ['href' => '/contact', 'label' => 'Contact Us'],
    'background' => 'primary',
])

@php
    $backgrounds = [
        'primary' => 'bg-primary-600 dark:bg-primary-700',
        'accent' => 'bg-accent-600 dark:bg-accent-700',
        'neutral' => 'bg-neutral-900 dark:bg-neutral-800',
    ];
@endphp

<section class="{{ $backgrounds[$background] ?? $backgrounds['primary'] }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4 tracking-tight">{{ $title }}</h2>
            <p class="text-lg text-white/80 mb-8 leading-relaxed">{{ $subtitle }}</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ $primaryCta['href'] }}" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-white text-primary-700 hover:bg-neutral-100 px-6 py-3 text-base shadow-sm">
                    {{ $primaryCta['label'] }}
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ $secondaryCta['href'] }}" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-transparent border border-white/30 text-white hover:bg-white/10 px-6 py-3 text-base">
                    {{ $secondaryCta['label'] }}
                </a>
            </div>
        </div>
    </div>
</section>
