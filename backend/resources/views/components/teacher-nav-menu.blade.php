@php
    $mainFeatures = [];
    $classTeacherFeatures = [];
    $subjectTeacherFeatures = [];
    $showClassDivider = false;
    $showSubjectDivider = false;

    $mainFeatures[] = ['href' => route('teacher.dashboard'), 'icon' => 'layout-dashboard', 'label' => 'Dashboard'];

    if ($hasClassAssignment()) {
        $classTeacherFeatures = [
            ['href' => route('teacher.class.attendance'), 'icon' => 'school', 'label' => 'My Class'],
            ['href' => route('teacher.attendance'), 'icon' => 'calendar-check', 'label' => 'Attendance'],
            ['href' => route('teacher.report-cards.index'), 'icon' => 'file-text', 'label' => 'Report Cards'],
            ['href' => route('teacher.class-performance'), 'icon' => 'bar-chart-3', 'label' => 'Class Performance'],
        ];
        $showClassDivider = true;
    }

    if ($hasSubjectAssignments()) {
        $subjectTeacherFeatures = [
            ['href' => route('teacher.assignments'), 'icon' => 'book-open', 'label' => 'My Subjects'],
            ['href' => route('teacher.scores'), 'icon' => 'calculator', 'label' => 'Enter Scores'],
        ];
        $showSubjectDivider = true;
    }

    $features = $mainFeatures;
    if ($showClassDivider && $classTeacherFeatures) {
        $features[] = ['isDivider' => true, 'label' => 'Class Teacher'];
        $features = array_merge($features, $classTeacherFeatures);
    }
    if ($showSubjectDivider && $subjectTeacherFeatures) {
        $features[] = ['isDivider' => true, 'label' => 'Subject Teacher'];
        $features = array_merge($features, $subjectTeacherFeatures);
    }
    $features[] = ['label' => 'Logout', 'isLogout' => true];
@endphp

@foreach($features as $feature)
    @if($feature['isLogout'] ?? false)
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="font-semibold">{{ $feature['label'] }}</span>
            </button>
        </form>
    @elseif($feature['isDivider'] ?? false)
        <div class="px-3 py-1.5">
            <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ $feature['label'] }}</span>
        </div>
        <hr class="border-neutral-200 dark:border-neutral-700">
    @else
        <x-layout.sidebar-item :href="$feature['href']" :icon="$feature['icon']" :label="$feature['label']" />
    @endif
@endforeach