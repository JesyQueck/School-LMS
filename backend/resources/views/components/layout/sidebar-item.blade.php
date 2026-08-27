@props([
    'href' => '#',
    'icon' => 'circle',
    'label' => 'Menu Item',
    'active' => null,
    'method' => 'GET',
    'emphasis' => false,
])

@php
    $iconPaths = [
        'layout-dashboard' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10m-2 2h-4m4 0h4',
        'layout-grid' => 'M3 3a1 1 0 011-1h7v7H4a1 1 0 01-1-1V3zm0 10a1 1 0 011-1h7v7H4a1 1 0 01-1-1v-6zm10 0a1 1 0 011-1h7v7h-7a1 1 0 01-1-1v-6zm0-10a1 1 0 011-1h7v7h-7a1 1 0 01-1-1V3z',
        'school' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7',
        'graduation-cap' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7',
        'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'clipboard-list' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'wallet' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'file-text' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'book-open' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'megaphone' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'settings' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'search' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        'filter' => 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z',
        'plus' => 'M12 4v16m8-8H4',
        'pencil' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
        'trash-2' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        'eye-off' => 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.059 10.059 0 013.999-5.377m3.071-6.293A10.052 10.052 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-3.428 5.384 M15 12a3 3 0 11-6 0 3 3 0 016 0z M3 3l18 18',
        'moon' => 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
        'sun' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
        'chevron-down' => 'M19 9l-7 7-7-7',
        'more-horizontal' => 'M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z',
        'log-out' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
        'users-2' => 'M17 21v-2m4 4v-2m0 0h-4m4 0H9l-5 5V5h2l2 3v-2a4 4 0 118 0 4 4 0 011 0z',
        'user' => 'M12 12a2 2 0 114 0 2 2 0 01-4 0zm0 0C16.93 12 21 7.933 21 2.5V2a1 1 0 00-1-1H4a1 1 0 00-1 1v.5C3 7.933 7.07 12 12 12z',
        'camera' => 'M3 7a2 2 0 012-2h6l2 2h5a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm7 3a3 3 0 11-6 0 3 3 0 016 0z',
        'user-check' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 15v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4 0h-4m4 0v-4',
        'calculator' => 'M9 2a1 1 0 011 1v12a1 1 0 01-2 0V3a1 1 0 011-1zm6 0a1 1 0 011 1v12a1 1 0 01-2 0V3a1 1 0 011-1z M5 20a2 2 0 012-2h8a2 2 0 012 2v1H5v1zm7-9a1 1 0 11-2 0 1 1 0 012 0z',
        'bar-chart-3' => 'M3 3v18h18M9 12h6M9 9h6M9 6h6',
        'flask' => 'M9 3v8m0-8h6m-6 8h6M12 3v11m-6 4h12a6 6 0 016 6H6a6 6 0 016-6z',
        'award' => 'M12 2l4 5.5H20a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2v-9a2 2 0 012-2h6L12 2z M12 14v6m0-6v6',
        'calendar-check' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    ];

    $href = $method === 'POST' ? route('logout') : $href;
    $method = $method === 'POST' ? 'POST' : 'GET';

    $path = ltrim(parse_url($href, PHP_URL_PATH), '/');
    if ($active !== null) {
        $isActive = (bool) $active;
    } else {
        $isActive = request()->is($path);
        if (! $isActive && strpos($path, '/') !== false) {
            $isActive = request()->is($path.'/*');
        }
    }
@endphp

@php
    $baseClasses = 'flex items-center w-full gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-neutral-300 hover:bg-white/5 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-primary-700 focus:ring-white transition-colors duration-150';
    $activeClasses = 'bg-white/8 border-l-4 border-l-white text-white font-semibold';
    if ($emphasis) {
        $emphasisClasses = 'border border-danger-500/50 hover:border-danger-400 hover:bg-danger-500/10 hover:text-danger-300 focus:ring-danger-400';
    } else {
        $emphasisClasses = '';
    }
@endphp

@if($method === 'POST')
    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <button type="submit" class="{{ $baseClasses }} {{ $emphasisClasses }} {{ $isActive ? $activeClasses : '' }}" @if($isActive) aria-current="page" @endif>
            <svg class="h-5 w-5 flex-shrink-0 {{ $emphasis ? 'text-danger-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$icon] ?? '' }}"/>
            </svg>
            <span class="{{ $emphasis ? 'text-danger-300' : '' }}">{{ $label }}</span>
        </button>
    </form>
@else
    <a href="{{ $href }}"
       class="{{ $baseClasses }} {{ $emphasisClasses }} {{ $isActive ? $activeClasses : '' }}"
       @if($isActive) aria-current="page" @endif>
        <svg class="h-5 w-5 flex-shrink-0 {{ $emphasis ? 'text-danger-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$icon] ?? '' }}"/>
        </svg>
        <span class="{{ $emphasis ? 'text-danger-300' : '' }}">{{ $label }}</span>
    </a>
@endif
