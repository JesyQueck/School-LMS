@use('Illuminate\Support\Str')
@props(['user' => null, 'editable' => true, 'formAction' => null, 'destroyAction' => null])

@php
    $user = $user ?? auth()->user();
    $formAction = $formAction ?? route('profile.photo.update');
    $destroyAction = $destroyAction ?? route('profile.photo.destroy');
    $photoUrl = $user && $user->profile_photo
        ? (Str::startsWith($user->profile_photo, ['http://', 'https://'])
            ? $user->profile_photo
            : asset('storage/' . $user->profile_photo))
        : null;
@endphp

<div class="flex items-center gap-4">
    @if ($photoUrl)
        <img src="{{ $photoUrl }}" alt="{{ $user->name ?? 'Profile' }}"
             class="h-16 w-16 flex-shrink-0 rounded-full object-cover ring-2 ring-neutral-200 dark:ring-neutral-700">
    @else
        @php
            $initial = $user ? Str::upper(Str::substr($user->name ?? 'U', 0, 1)) : 'U';
        @endphp
        <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30 text-xl font-medium text-primary-700 dark:text-primary-300 ring-2 ring-neutral-200 dark:ring-neutral-700">
            {{ $initial }}
        </div>
    @endif

    @if ($editable)
        <div class="flex flex-col gap-1.5">
            <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M3 9a2 2 0 0 1 2-2h1.5l1-1h4l1 1H16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Choose photo
                    <input type="file" name="photo" accept="image/*" class="sr-only"
                           onchange="this.form.requestSubmit()">
                </label>
            </form>

            @if ($photoUrl)
                <form method="POST" action="{{ $destroyAction }}" enctype="multipart/form-data">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                        Remove photo
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
