@props(['label' => null, 'active' => false])

<div class="mb-1">
    @if ($label)
        <div class="px-3 mb-1.5 flex items-center">
            <span class="text-[10px] font-semibold uppercase tracking-wider {{ $active ? 'text-white' : 'text-neutral-300' }}">
                {{ $label }}
            </span>
            <span class="ml-2 h-px flex-1 bg-white/10"></span>
        </div>
    @endif

    {{ $slot }}
</div>
