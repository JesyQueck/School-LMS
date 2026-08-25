<x-layouts.app title="Student Fee Details">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.finance') }}">Finance</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Fee Details</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">{{ $studentFee->student->full_name }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $studentFee->student->admission_no }} &middot; {{ $studentFee->student->schoolClass->name ?? 'N/A' }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 space-y-6">
            <x-ui.card>
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Fee Summary</h3>
                </div>
                 <div class="p-3 grid grid-cols-2 gap-3 text-xs">
                    <div class="flex justify-between col-span-2"><span class="text-neutral-500 dark:text-neutral-400">Total Fees</span><span class="font-medium text-neutral-900 dark:text-white">₦{{ number_format($studentFee->amount_expected, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500 dark:text-neutral-400">Paid</span><span class="font-medium text-success-600 dark:text-success-400">₦{{ number_format($paid, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500 dark:text-neutral-400">Outstanding</span><span class="font-semibold {{ $outstanding > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">₦{{ number_format($outstanding, 2) }}</span></div>
                    <div class="flex justify-between col-span-2">
                        <span class="text-neutral-500 dark:text-neutral-400">Status</span>
                        <span class="font-semibold text-neutral-900 dark:text-white uppercase">{{ $status }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Fee Breakdown</h3>
                </div>
                <div class="p-3">
                    <div class="flex justify-between text-xs py-1.5 border-b border-neutral-100 dark:border-dark-border">
                        <span class="text-neutral-700 dark:text-neutral-300">{{ $studentFee->feeType->name ?? 'Fee' }}</span>
                        <span class="font-medium text-neutral-900 dark:text-white">₦{{ number_format($studentFee->amount_expected, 2) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Payment History</h3>
                </div>
                <div class="p-3">
                    @forelse($studentFee->payments->sortByDesc('payment_date') as $payment)
                        <div class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-dark-border last:border-0">
                            <div>
                                <p class="text-xs font-medium text-neutral-900 dark:text-white">{{ $payment->payment_date?->format('d M Y') }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ ucfirst($payment->payment_method ?? 'cash') }} &middot; {{ $payment->reference ?? 'No reference' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold text-success-600 dark:text-success-400">₦{{ number_format($payment->amount_paid, 2) }}</p>
                                <a href="{{ route('admin.finance.payments.receipt', $payment) }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">Receipt</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">No payments recorded.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-5">
            <x-ui.card class="mt-6 lg:mt-0">
            <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Record Payment</h3>
            </div>
            <form method="POST" action="{{ route('admin.finance.payments.store') }}" class="p-3 space-y-3">
                @csrf
                <input type="hidden" name="student_fee_id" value="{{ $studentFee->id }}">
                <div class="flex justify-between text-xs text-neutral-500 dark:text-neutral-400"><span>Maximum payment</span><span>₦{{ number_format($outstanding, 2) }}</span></div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Amount (₦) <span class="text-danger-500">*</span></label>
                    <input name="amount_paid" type="number" step="0.01" max="{{ $outstanding }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Payment Date <span class="text-danger-500">*</span></label>
                    <input name="payment_date" type="date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Reference</label>
                    <input name="reference" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-1.5 rounded-lg shadow-sm text-sm">Record Payment</button>
            </form>
        </x-ui.card>
        </div>
    </div>
</x-layouts.app>
