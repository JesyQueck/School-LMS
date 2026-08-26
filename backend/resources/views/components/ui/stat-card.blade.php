@props(['label' => '', 'value' => '', 'trend' => null, 'icon' => '', 'compact' => false])

@php
    $trendColors = [
        'up' => 'text-success-600 dark:text-success-400',
        'down' => 'text-danger-600 dark:text-danger-400',
    ];

    $iconPaths = [
        'layout-dashboard' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10m-2 2h-4m4 0h4',
        'school' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7',
        'graduation-cap' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14v7',
        'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'clipboard-list' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'wallet' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'file-text' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'book-open' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'calendar-check' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'megaphone' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'users-2' => 'M17 21v-2m4 4v-2m0 0h-4m4 0H9l-5 5V5h2l2 3v-2a4 4 0 118 0 4 4 0 011 0z',
        'user' => 'M12 12a2 2 0 114 0 2 2 0 01-4 0zm0 0C16.93 12 21 7.933 21 2.5V2a1 1 0 00-1-1H4a1 1 0 00-1 1v.5C3 7.933 7.07 12 12 12z',
        'user-check' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 15v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4 0h-4m4 0v-4',
        'settings' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'log-out' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
        'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'award' => 'M12 2l4 5.5H20a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2v-9a2 2 0 012-2h6L12 2z M12 14v6m0-6v6',
        'layout-grid' => 'M3.75 3.375c0-1.036.814-1.875 1.875-1.875h4.5c1.036 0 1.875.814 1.875 1.875v4.5A1.875 1.875 0 019.375 9.75h-4.5A1.875 1.875 0 013.75 7.875v-4.5zM12 9.375c0-1.036.814-1.875 1.875-1.875h4.5c1.036 0 1.875.814 1.875 1.875v4.5a1.875 1.875 0 01-1.875 1.875h-4.5a1.875 1.875 0 01-1.875-1.875v-4.5zM3.75 15.75c0-1.036.814-1.875 1.875-1.875h4.5c1.036 0 1.875.814 1.875 1.875v4.5a1.875 1.875 0 01-1.875 1.875h-4.5a1.875 1.875 0 01-1.875-1.875v-4.5zM12 15.75c0-1.036.814-1.875 1.875-1.875h4.5c1.036 0 1.875.814 1.875 1.875v4.5a1.875 1.875 0 01-1.875 1.875h-4.5a1.875 1.875 0 01-1.875-1.875v-4.5z',
    ];
@endphp

 <div class="{{ $compact ? 'bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-3 shadow-sm' : 'bg-white dark:bg-dark-surface rounded-2xl border-2 border-neutral-200 dark:border-dark-border p-6 shadow-premium hover:shadow-premium-lg transition-all duration-300' }}">
     <div class="flex flex-col">
         <p class="{{ $compact ? 'text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase' : 'text-sm font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide' }}">{{ $label }}</p>
         <div class="mt-1 flex items-center gap-2">
             <p class="{{ $compact ? 'text-2xl font-bold text-neutral-900 dark:text-white' : 'text-4xl font-bold text-neutral-900 dark:text-white tracking-tight' }}">{{ $value }}</p>
             @if($icon)
                 <div class="{{ $compact ? 'h-6 w-6 rounded-lg bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center' : 'h-10 w-10 rounded-xl bg-primary-200 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-neutral-200 dark:ring-dark-border flex items-center justify-center' }}">
                     <svg class="{{ $compact ? 'h-3 w-3' : 'h-5 w-5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$icon] ?? $icon }}"/></svg>
                 </div>
             @endif
         </div>
         @if($trend)
             <p class="mt-2 flex items-center gap-1.5 text-sm font-semibold {{ $trendColors[$trend['direction']] ?? 'text-neutral-500' }}">
                 <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $trend['direction'] === 'up' ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                 {{ $trend['value'] }}
             </p>
         @endif
     </div>
 </div>
