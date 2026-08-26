@props(['compact' => false])

@php
    $schoolName = config('school.name', config('app.name', 'Greenfield Academy'));
@endphp

<footer class="bg-primary-800 text-neutral-100 border-t border-white/10 px-4 sm:px-6 lg:px-8 py-3 flex-shrink-0">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
        <p>&copy; {{ date('Y') }} {{ $schoolName }}. All rights reserved.</p>
        @if (! $compact)
            <p class="text-neutral-200/80">
                {{ config('school.email', 'info@greenfieldacademy.edu') }}
                &middot;
                {{ config('school.phone', '+234 800 000 0000') }}
            </p>
        @endif
    </div>
</footer>
