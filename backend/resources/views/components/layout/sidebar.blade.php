@props(['title' => null])

@php
    $user = auth()->user();
    $role = $user?->role;

    $roleLabels = [
        'admin' => 'System Administrator',
        'teacher' => 'Teacher',
        'student' => 'Student',
        'parent' => 'Parent',
    ];
    $roleLabel = $user ? ($roleLabels[$role] ?? ucfirst((string) $role)) : 'User';

    $schoolName = config('school.name', config('app.name', 'Greenfield Academy'));

    $pathIsActive = function (string $href): bool {
        $path = ltrim(parse_url($href, PHP_URL_PATH), '/');
        if ($path === '') {
            return false;
        }
        if (request()->is($path) || request()->is($path.'/*')) {
            return true;
        }
        if (strpos($path, '/') !== false) {
            return request()->is($path.'/*');
        }
        return false;
    };

    $item = function (string $label, string $href, string $icon = 'circle', string $method = 'GET') use ($pathIsActive) {
        $itemPath = ltrim(parse_url($href, PHP_URL_PATH), '/');
        $active = $pathIsActive($href);
        return [
            'label' => $label,
            'href' => $href,
            'icon' => $icon,
            'method' => $method,
            'active' => $active,
            'path' => $itemPath,
        ];
    };

    $sectionIsActive = function (array $items) {
        return collect($items)->contains(fn ($it) => $it['active']);
    };

    $sections = [];
    $settingsItems = [];
    $logoutItem = $item('Logout', route('logout'), 'log-out', 'POST');

    if ($role === 'admin') {
        $sections = [
            [
                'label' => 'MAIN',
                'items' => [
                    $item('Dashboard', route('admin.dashboard'), 'layout-dashboard'),
                ],
            ],
            [
                'label' => 'ACADEMICS',
                'items' => [
                    $item('Students', route('admin.students'), 'users'),
                    $item('Teachers', route('admin.teachers'), 'graduation-cap'),
                    $item('Classes', route('admin.classes'), 'school'),
                    $item('Academic Structure', route('admin.academic'), 'calendar'),
                    $item('Subjects', route('admin.subjects.index'), 'book-open'),
                    $item('Timetable', route('admin.timetable.index'), 'calendar-check'),
                    $item('Report Cards', route('admin.report-cards.index'), 'file-text'),
                ],
            ],
            [
                'label' => 'FINANCE',
                'items' => [
                    $item('Fees / Payments', route('admin.finance'), 'wallet'),
                ],
            ],
            [
                'label' => 'COMMUNICATION',
                'items' => [
                    $item('Announcements', route('admin.announcements'), 'megaphone'),
                ],
            ],
            [
                'label' => 'SYSTEM',
                'items' => [
                    $item('Accounts', route('admin.accounts.index'), 'user-check'),
                    $item('Audit Logs', route('admin.audit-logs.index'), 'clipboard'),
                ],
            ],
        ];
    } elseif ($role === 'teacher') {
        $sections = [
            [
                'label' => 'MAIN',
                'items' => [
                    $item('Dashboard', route('teacher.dashboard'), 'layout-dashboard'),
                ],
            ],
            [
                'label' => 'TEACHING',
                'items' => [
                    $item('My Classes', route('teacher.classes.index'), 'school'),
                    $item('Attendance', route('teacher.attendance'), 'calendar'),
                    $item('Results', route('teacher.results'), 'clipboard-list'),
                    $item('Report Cards', route('teacher.report-cards.index'), 'file-text'),
                    $item('Timetable', route('teacher.timetable'), 'calendar-check'),
                ],
            ],
            [
                'label' => 'COMMUNICATION',
                'items' => [
                    $item('Announcements', route('teacher.announcements'), 'megaphone'),
                ],
            ],
        ];

    } elseif ($role === 'student') {
        $sections = [
            [
                'label' => 'MAIN',
                'items' => [
                    $item('Dashboard', route('student.dashboard'), 'layout-dashboard'),
                ],
            ],
            [
                'label' => 'ACADEMICS',
                'items' => [
                    $item('Timetable', route('student.timetable'), 'calendar'),
                    $item('Attendance', route('student.attendance'), 'calendar-check'),
                    $item('Report Cards', route('student.report-cards'), 'file-text'),
                ],
            ],
            [
                'label' => 'FINANCE',
                'items' => [
                    $item('Fees', route('student.fees'), 'wallet'),
                ],
            ],
        ];

    } elseif ($role === 'parent') {
        $sections = [
            [
                'label' => 'MAIN',
                'items' => [
                    $item('Dashboard', route('parent.dashboard'), 'layout-dashboard'),
                ],
            ],
            [
                'label' => 'CHILDREN',
                'items' => [
                    $item('Children', route('parent.dashboard'), 'users'),
                    $item('Timetable', route('parent.timetable'), 'calendar'),
                ],
            ],
        ];

    } else {
        $sections = [];
        $settingsItems = [];
    }

    if (in_array($role, ['admin', 'teacher', 'student', 'parent'])) {
        $settingsItems = [
            $item('Profile', route('settings.profile'), 'camera'),
            $item('Change Password', route('password.change'), 'settings'),
        ];
    }

    if (! empty($settingsItems)) {
        $sections[] = ['label' => 'SETTINGS', 'items' => $settingsItems];
    }

    $logoPath = config('school.logo');
    $logoExists = $logoPath && file_exists(public_path($logoPath));
    $schoolInitial = strtoupper(collect(explode(' ', $schoolName))->first()[0] ?? 'S');
@endphp

<div class="flex flex-col h-full text-neutral-100">
    {{-- Branding --}}
    <div class="flex items-center justify-between gap-3 px-4 py-4 border-b border-white/10 bg-primary-800">
        <div class="flex items-center gap-3">
            @if ($logoExists)
                <img src="{{ asset($logoPath) }}" alt="{{ $schoolName }}" class="h-14 w-auto max-w-[160px] object-contain" width="160" height="56">
            @else
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-white/15 text-xl font-semibold text-white">
                    {{ $schoolInitial }}
                </div>
            @endif

            <span class="text-lg font-semibold tracking-tight text-white">{{ $schoolName }}</span>
        </div>

        <label for="sidebar-menu-checkbox"
               class="lg:hidden cursor-pointer rounded-xl p-1.5 text-white hover:bg-white/10 focus-visible-ring transition-colors duration-200 shrink-0"
               aria-label="Close menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </label>
    </div>

    {{-- User profile --}}
    <div class="px-4 py-3">
        <x-layout.sidebar-user :user="$user" />
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto" aria-label="Main navigation">
        @foreach ($sections as $section)
            <x-layout.sidebar-section :label="$section['label']" :active="$sectionIsActive($section['items'])">
                @foreach ($section['items'] as $it)
                    <x-layout.sidebar-item
                        :href="$it['href']"
                        :icon="$it['icon']"
                        :label="$it['label']"
                        :active="$it['active']"
                        method="{{ $it['method'] }}" />
                @endforeach
            </x-layout.sidebar-section>
        @endforeach
    </nav>

    {{-- Bottom actions --}}
    <div class="p-2 border-t border-white/10 bg-primary-800">
        <x-layout.sidebar-item
            :href="$logoutItem['href']"
            :icon="$logoutItem['icon']"
            :label="$logoutItem['label']"
            :active="$logoutItem['active']"
            :emphasis="true"
            method="{{ $logoutItem['method'] }}" />
    </div>
</div>
