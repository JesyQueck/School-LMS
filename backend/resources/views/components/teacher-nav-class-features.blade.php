@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2 ' . $class]) }}>
    @php
        $features = [
            ['href' => '/teacher/attendance', 'icon' => 'calendar', 'label' => 'Attendance'],
            ['href' => '/teacher/results', 'icon' => 'clipboard-list', 'label' => 'Enter Results'],
            ['href' => '/teacher/class-performance', 'icon' => 'bar-chart-3', 'label' => 'Class Performance'],
            ['href' => '/teacher/parents', 'icon' => 'users', 'label' => 'Parent Communication'],
            ['href' => '/teacher/assessments', 'icon' => 'clipboard', 'label' => 'Behaviour Assessment'],
        ];
    @endphp

    @foreach($features as $feature)
        <x-layout.sidebar-item :href="$feature['href']" :icon="$feature['icon']" :label="$feature['label']" />
    @endforeach
</div>
