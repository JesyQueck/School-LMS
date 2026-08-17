<x-layouts.app title="Academic Structure">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Academic</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Academic Structure</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Configure sessions, terms, subjects, and class mappings.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="space-y-6">
        {{-- Sessions --}}
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Sessions</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">A session auto-creates 3 terms. Set the active session with "Make Current".</p>
            </div>
            <form method="POST" action="{{ route('admin.academic.sessions.store') }}" class="p-6 border-b border-neutral-200 dark:border-dark-border">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Session Name <span class="text-danger-500">*</span></label>
                        <input name="name" type="text" placeholder="e.g. 2027/2028" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Start Date <span class="text-danger-500">*</span></label>
                        <input name="start_date" type="date" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">End Date <span class="text-danger-500">*</span></label>
                        <input name="end_date" type="date" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-base">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm">Create Session</button>
                    </div>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Start</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">End</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Terms</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                <form id="edit-session-{{ $session->id }}" method="POST" action="{{ route('admin.academic.sessions.update', $session) }}" class="contents">
                                    @csrf
                                    @method('PUT')
                                    <td class="px-6 py-4">
                                        <input type="text" name="name" value="{{ old('name', $session->name) }}"
                                            class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        @error('name')
                                            <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="date" name="start_date" value="{{ old('start_date', $session->start_date?->toDateString()) }}"
                                            class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        @error('start_date')
                                            <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="date" name="end_date" value="{{ old('end_date', $session->end_date?->toDateString()) }}"
                                            class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        @error('end_date')
                                            <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $session->terms->count() }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($session->is_current)
                                            <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Current</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-2.5 py-0.5 text-xs font-medium text-neutral-700 dark:text-neutral-300">Archived</span>
                                        @endif
                                    </td>
                                </form>
                                <td class="px-6 py-4">
                                    <div class="flex items-end gap-2">
                                        <button type="submit" form="edit-session-{{ $session->id }}"
                                            class="bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 font-medium px-3 py-1.5 rounded-lg text-sm">Save</button>
                                        @unless($session->is_current)
                                            <form method="POST" action="{{ route('admin.academic.sessions.current', $session) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm">Make Current</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No sessions configured yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        {{-- Terms --}}
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Terms</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Set each term's real dates and choose the active term.</p>
            </div>
            <div class="divide-y divide-neutral-200 dark:divide-dark-border">
                @forelse($sessions as $session)
                    <div class="p-6">
                        <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">{{ $session->name }}</h4>
                        <div class="space-y-3">
                            @forelse($session->terms as $term)
                                <div class="flex flex-col sm:flex-row sm:items-end gap-3 p-3 rounded-lg border border-neutral-200 dark:border-dark-border">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">
                                            {{ $term->name }}
                                            @if($term->is_current)
                                                <span class="ml-2 inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2 py-0.5 text-[10px] font-medium text-success-700 dark:text-success-300">Current</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                                            {{ $term->start_date?->toDateString() ?? 'No start' }} → {{ $term->end_date?->toDateString() ?? 'No end' }}
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.academic.terms.update', $term) }}" class="flex items-end gap-2">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Start</label>
                                            <input type="date" name="start_date" value="{{ $term->start_date?->toDateString() }}" class="rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">End</label>
                                            <input type="date" name="end_date" value="{{ $term->end_date?->toDateString() }}" class="rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                                        </div>
                                        <button type="submit" class="bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 font-medium px-3 py-1.5 rounded-lg text-sm">Save</button>
                                    </form>
                                    @unless($term->is_current)
                                        <form method="POST" action="{{ route('admin.academic.terms.current', $term) }}">
                                            @csrf
                                            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm">Make Current</button>
                                        </form>
                                    @endunless
                                </div>
                            @empty
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">No terms.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-sm text-neutral-500 dark:text-neutral-400">No sessions yet.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
