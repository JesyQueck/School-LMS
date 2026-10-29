@php
    $name = $period?->name ?? '';
    $startTime = $period?->start_time ? \Carbon\Carbon::parse($period->start_time)->format('H:i') : '';
    $endTime = $period?->end_time ? \Carbon\Carbon::parse($period->end_time)->format('H:i') : '';
    $isBreak = $period?->is_break ?? false;
    $type = $period?->type ?? ($isBreak ? 'break' : 'teaching');
    $periodNumber = $period?->period_number ?? $periodIndex;
    $sortOrder = $period?->sort_order ?? $periodIndex;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 items-end gap-3 p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-dark-border">
    <div class="sm:col-span-2 md:col-span-1">
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Name</label>
        <input type="text" name="schedules[{{ $index }}][periods][{{ $periodIndex }}][name]" value="{{ $name }}" placeholder="e.g. Period {{ $periodNumber }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
    </div>

    <div class="md:col-span-1">
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Start</label>
        <input type="time" name="schedules[{{ $index }}][periods][{{ $periodIndex }}][start_time]" value="{{ $startTime }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
    </div>

    <div class="md:col-span-1">
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">End</label>
        <input type="time" name="schedules[{{ $index }}][periods][{{ $periodIndex }}][end_time]" value="{{ $endTime }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
    </div>

    <div class="md:col-span-1">
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Type</label>
        <select name="schedules[{{ $index }}][periods][{{ $periodIndex }}][type]" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="teaching" {{ $type === 'teaching' ? 'selected' : '' }}>Teaching</option>
            <option value="break" {{ $type === 'break' ? 'selected' : '' }}>Break</option>
            <option value="activity" {{ $type === 'activity' ? 'selected' : '' }}>Activity</option>
        </select>
    </div>

    <div class="md:col-span-1 flex items-end pb-0.5">
        <label class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400 cursor-pointer">
            <input type="checkbox" name="schedules[{{ $index }}][periods][{{ $periodIndex }}][is_break]" value="1" {{ $isBreak ? 'checked' : '' }} class="rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
            Is Break
        </label>
    </div>

    <input type="hidden" name="schedules[{{ $index }}][periods][{{ $periodIndex }}][period_number]" value="{{ $periodNumber }}">
    <input type="hidden" name="schedules[{{ $index }}][periods][{{ $periodIndex }}][sort_order]" value="{{ $sortOrder }}">
</div>
