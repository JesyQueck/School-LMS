@php
    $classTeacherFeatures = [
        ['href' => '/teacher/attendance', 'icon' => 'calendar', 'label' => 'Attendance'],
        ['href' => '/teacher/results', 'icon' => 'clipboard-list', 'label' => 'Enter Results'],
        ['href' => '/teacher/class-performance', 'icon' => 'bar-chart-3', 'label' => 'Class Performance'],
        ['href' => '/teacher/parents', 'icon' => 'users', 'label' => 'Parent Communication'],
        ['href' => '/teacher/assessments', 'icon' => 'clipboard', 'label' => 'Behaviour Assessment'],
    ];

    $subjectTeacherFeatures = [
        ['href' => '/teacher/attendance', 'icon' => 'calendar', 'label' => 'Enter Attendance'],
        ['href' => '/teacher/results', 'icon' => 'clipboard-list', 'label' => 'Enter Results'],
    ];

    $sharedFeatures = [
        ['href' => '/teacher/dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
        ['href' => '/teacher/profile', 'icon' => 'user', 'label' => 'Profile'],
    ];
@endphp

@foreach($sharedFeatures as $feature)
    <x-layout.sidebar-item :href="$feature['href']" :icon="$feature['icon']" :label="$feature['label']" />
@endforeach

@if($isClassTeacher || $isSubjectTeacher)
    <x-ui.divider class="my-2" />
@endif

@if($isSubjectTeacher)
    <x-layout.sidebar-item href="/teacher/assignments" icon="book-open" label="My Subjects" />
@endif

@if($isClassTeacher)
    <x-teacher-nav-class-features />
@endif