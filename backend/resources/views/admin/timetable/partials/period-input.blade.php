@php
    $fieldName = $showDelete ?? false ? "new_periods[{$index}]" : "periods[{$index}]";
@endphp
<div class="grid grid-cols-5 gap-2 items-end py-1.5">
    <div class="col-span-2">
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-0.5">Name</label>
        <input type="text" name="periods[{{ $index }}][name]" value="{{ $period->name ?? '' }}" placeholder="e.g. Period 1" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
    </div>
    <input type="hidden" name="periods[{{ $index }}][period_number]" value="{{ $period->period_number }}">
    <input type="hidden" name="periods[{{ $index }}][sort_order]" value="{{ $period->sort_order ?? $index }}">
    <div>
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-0.5">Start</label>
        <input type="time" name="periods[{{ $index }}][start_time]" value="{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
    </div>
    <div>
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-0.5">End</label>
        <input type="time" name="periods[{{ $index }}][end_time]" value="{{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1 text-sm">
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400">
            <input type="checkbox" name="periods[{{ $index }}][is_break]" value="1" {{ $period->is_break ? 'checked' : '' }} class="rounded border-neutral-300 dark:border-dark-border">
            Break
        </label>
    </div>
</div>
