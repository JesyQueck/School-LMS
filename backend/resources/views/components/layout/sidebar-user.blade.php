@use('Illuminate\Support\Str')
@props(['user' => null])

@php
    $user = $user ?? auth()->user();
    $roleLabels = [
        'admin' => 'System Administrator',
        'teacher' => 'Teacher',
        'student' => 'Student',
        'parent' => 'Parent',
    ];
    $roleLabel = $user ? ($roleLabels[$user->role] ?? ucfirst((string) ($user->role ?? ''))) : 'User';
@endphp

<div class="flex flex-col items-center text-center">
    @if ($user && $user->profile_photo)
        @php
            $photoUrl = Str::startsWith($user->profile_photo, ['http://', 'https://'])
                ? $user->profile_photo
                : asset('storage/' . $user->profile_photo);
        @endphp
        <img src="{{ $photoUrl }}" alt="{{ $user->name }}"
             class="h-16 w-16 rounded-full object-cover ring-2 ring-white/20 mb-2">
    @else
        @php
            $initial = $user ? Str::upper(Str::substr($user->name ?? 'U', 0, 1)) : 'U';
        @endphp
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/15 text-xl font-semibold text-white ring-2 ring-white/20 mb-2">
            {{ $initial }}
        </div>
    @endif

    <p class="text-sm font-semibold text-white truncate">
        {{ $user->name ?? 'Guest' }}
    </p>
    <p class="text-xs text-neutral-300 truncate">
        {{ $roleLabel }}
    </p>
</div>
