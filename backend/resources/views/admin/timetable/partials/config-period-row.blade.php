<div class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border">
    <div>
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Period Name</label>
        <input type="text" name="periods[{{ $index }}][name]" value="{{ $period->name }}" placeholder="e.g. Period {{ $period->period_number }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
    </div>
    <div>
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Start Time</label>
        <input type="time" name="periods[{{ $index }}][start_time]" value="{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
    </div>
    <div>
        <label class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">End Time</label>
        <input type="time" name="periods[{{ $index }}][end_time]" value="{{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}" class="w-full rounded border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" required>
    </div>
    <div class="flex items-end pb-2">
        <label class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400 cursor-pointer">
            <input type="checkbox" name="periods[{{ $index }}][is_break]" value="1" {{ $period->is_break ? 'checked' : '' }} class="rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500">
            Break
        </label>
    </div>
    <input type="hidden" name="periods[{{ $index }}][period_number]" value="{{ $period->period_number }}">
    <input type="hidden" name="periods[{{ $index }}][sort_order]" value="{{ $period->sort_order ?? $index }}">
    <input type="hidden" name="periods[{{ $index }}][is_break]" value="{{ $period->is_break ? '1' : '0' }}">
</div>
