<x-layouts.app title="Student Fee Details">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('bursar.student-fees') }}">Student Fee Payments</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>{{ $studentFee->student->full_name }}</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">{{ $studentFee->student->full_name }}</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $studentFee->student->schoolClass->name }} &middot; {{ $studentFee->feeType->name }} &middot; {{ $studentFee->term->name }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <x-ui.card class="mb-6">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Fee Summary</h3>
                </div>
                <div class="p-6 grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Expected</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">₦{{ number_format($studentFee->amount_expected, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Amount Paid</p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400">₦{{ number_format($paid, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Outstanding</p>
                        <p class="text-2xl font-bold {{ $outstanding > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">₦{{ number_format($outstanding, 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            {{-- Component Breakdown (for fee packages) --}}
            @if($studentFee->componentSnapshots->isNotEmpty())
                <x-ui.card class="mb-6">
                    <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Component Breakdown</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                            <thead class="bg-neutral-50 dark:bg-dark-surface">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Component</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                @foreach($studentFee->componentSnapshots as $snapshot)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $snapshot->component_name }}</td>
                                        <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $snapshot->component_description ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($snapshot->component_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-neutral-50 dark:bg-neutral-800/50 font-semibold">
                                    <td colspan="2" class="px-6 py-3 text-right text-sm text-neutral-900 dark:text-white">Total</td>
                                    <td class="px-6 py-3 text-sm text-neutral-900 dark:text-white text-right">₦{{ number_format($studentFee->componentSnapshots->sum('component_amount'), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            @endif

            {{-- Payment History --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Payment History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Receipt No.</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Recorded By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            @forelse($studentFee->payments->sortByDesc('payment_date') as $payment)
                                @php
                                    $isReversed = $payment->isReversed();
                                    $statusClass = $isReversed ? 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300' : ($isReversed ? '' : 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300');
                                    $statusLabel = $isReversed ? 'Reversed' : 'Valid';
                                @endphp
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors {{ $isReversed ? 'opacity-75' : '' }}">
                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $payment->receipt_number }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->payment_date?->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($payment->payment_method ?? 'Cash') }}</td>
                                    <td class="px-6 py-4 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($payment->amount_paid, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->reference ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->recordedBy->name ?? 'System' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-full {{ $statusClass }} px-2 py-0.5 text-xs font-medium">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <button type="button" data-view-receipt="{{ $payment->id }}" class="text-primary-600 dark:text-primary-400 hover:underline mr-3">View Receipt</button>
                                        @if(!$isReversed && $outstanding > 0)
                                            <button type="button" onclick="openReversalModal({{ $payment->id }}, '{{ $payment->receipt_number }}', {{ $payment->amount_paid }})" class="text-danger-600 dark:text-danger-400 hover:underline">Reverse</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            {{-- Student Info --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Student Information</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-2xl">
                            {{ substr($studentFee->student->full_name ?? $studentFee->student->admission_no, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $studentFee->student->full_name }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Admission: {{ $studentFee->student->admission_no }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm border-t border-neutral-100 dark:border-dark-border pt-3">
                        <div><span class="text-neutral-500 dark:text-neutral-400">Class:</span> <span class="font-medium text-neutral-900 dark:text-white">{{ $studentFee->student->schoolClass->name }}</span></div>
                        <div><span class="text-neutral-500 dark:text-neutral-400">Fee:</span> <span class="font-medium text-neutral-900 dark:text-white">{{ $studentFee->feeType->name }}</span></div>
                        <div><span class="text-neutral-500 dark:text-neutral-400">Term:</span> <span class="font-medium text-neutral-900 dark:text-white">{{ $studentFee->term->name }}</span></div>
                        <div><span class="text-neutral-500 dark:text-neutral-400">Status:</span>
                            <span class="inline-flex items-center rounded-full {{ $status === 'paid' ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : ($status === 'partial' ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300' : 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300') }} px-2 py-0.5 text-xs font-medium">{{ ucfirst($status) }}</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Fee Details --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Fee Details</h3>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Due Date</span>
                        <span class="font-medium text-neutral-900 dark:text-white">{{ $studentFee->feeType->due_date ? $studentFee->feeType->due_date->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Description</span>
                        <span class="font-medium text-neutral-900 dark:text-white">{{ $studentFee->feeType->description ?? '—' }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

        {{-- Reversal Modal --}}
    <div id="reversal-modal" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-50 overflow-y-auto p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md flex flex-col" style="font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <form method="POST" action="{{ route('bursar.payments.reverse') }}" class="flex-1 flex flex-col">
                @csrf
                <div class="px-4 py-3 border-b">
                    <h2 class="text-lg font-semibold" style="color: #000000;">Reverse Payment</h2>
                </div>
                <div class="p-4 overflow-y-auto space-y-4">
                    <input type="hidden" name="payment_id" id="reversal_payment_id">
                    <div>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white" id="reversal_receipt"></p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400" id="reversal_amount"></p>
                    </div>
                    <div class="border-t border-neutral-200 dark:border-dark-border pt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Reason <span class="text-danger-500">*</span></label>
                            <textarea name="reason" rows="4" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" placeholder="Enter the reason for reversing this payment (minimum 10 characters)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-t border-neutral-200 dark:border-dark-border flex gap-2">
                    <button type="button" onclick="closeReversalModal()" class="flex-1 px-3 py-1.5 text-sm text-neutral-600 dark:text-neutral-400 hover:underline">Cancel</button>
                    <button type="submit" class="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-danger-600 hover:bg-danger-700 rounded-lg">Reverse Payment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Receipt Modals --}}
    @foreach($studentFee->payments as $payment)
        @php
            $receiptStudent = $payment->studentFee->student ?? null;
            $receiptClass = $receiptStudent?->schoolClass ?? null;
            $receiptFeeType = $payment->studentFee->feeType ?? null;
            $receiptTerm = $payment->studentFee->term ?? null;
            $receiptAmount = number_format($payment->amount_paid, 2);
            $receiptMethod = ucfirst($payment->payment_method ?? 'Cash');
            $isReversed = $payment->isReversed();
        @endphp
        <div id="receipt-modal-{{ $payment->id }}" class="receipt-modal fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 p-3">
            <div class="bg-white dark:bg-dark-surface rounded-xl shadow-xl w-full max-w-xl mx-auto max-h-[90vh] flex flex-col">
                <div class="flex items-end justify-end px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <button type="button" data-close-modal="{{ $payment->id }}" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-6">
                    <div id="receipt-body-{{ $payment->id }}" class="receipt-body relative">
                        @php
                            $schoolName = config('school.name', 'Greenfield Academy');
                            $schoolAddress = config('school.address', '123 Education Lane, Victoria Island, Lagos');
                            $schoolPhone = config('school.phone', '+234 800 000 0000');
                            $schoolEmail = config('school.email', 'info@greenfieldacademy.edu');
                            $logoUrl = asset(config('school.logo', 'images/Logo.webp'));
                        @endphp
                        <img src="{{ $logoUrl }}" alt="{{ $schoolName }}" class="receipt-watermark">
                        <div class="center" style="margin-bottom: 16px;">
                            <img src="{{ $logoUrl }}" alt="{{ $schoolName }}" style="height: 48px; width: auto; display: block; margin: 0 auto 6px;">
                            <div class="sub" style="font-size: 16px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">{{ $schoolName }}</div>
                            <div class="label" style="font-size: 11px; color: #666; text-align: center;">{{ $schoolAddress }} &middot; {{ $schoolPhone }} &middot; {{ $schoolEmail }}</div>
                        </div>
                        <div class="center">
                            <div class="sub" style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 12px;">Official Payment Receipt</div>
                        </div>
                        <div class="line"></div>
                        <div class="row"><span class="label">Receipt No:</span><span>{{ $payment->receipt_number }}</span></div>
                        <div class="row"><span class="label">Date:</span><span>{{ $payment->payment_date?->format('d F Y') }}</span></div>
                        <div class="line"></div>
                        <div class="row"><span class="label">Student:</span><span>{{ $receiptStudent?->full_name ?? 'N/A' }}</span></div>
                        <div class="row"><span class="label">Admission No:</span><span>{{ $receiptStudent?->admission_no ?? 'N/A' }}</span></div>
                        <div class="row"><span class="label">Class:</span><span>{{ $receiptClass?->name ?? 'N/A' }}</span></div>
                        <div class="row"><span class="label">Fee:</span><span>{{ $receiptFeeType?->name ?? 'N/A' }}</span></div>
                        <div class="row"><span class="label">Term:</span><span>{{ $receiptTerm?->name ?? 'N/A' }}</span></div>
                        <div class="line"></div>
                        <div class="center" style="margin: 18px 0;">
                            <div class="label">Amount Paid</div>
                            <div class="amount">₦{{ $receiptAmount }}</div>
                        </div>
                        <div class="row"><span class="label">Payment Method:</span><span>{{ $receiptMethod }}</span></div>
                        <div class="row"><span class="label">Reference:</span><span>{{ $payment->reference ?? '—' }}</span></div>
                        <div class="row"><span class="label">Recorded By:</span><span>{{ $payment->recordedBy->name ?? 'System' }}</span></div>
                        @if($isReversed)
                            <div class="line"></div>
                            <div class="row"><span class="label">Status:</span><span class="text-danger-600 dark:text-danger-400 font-bold">REVERSED</span></div>
                            <div class="row"><span class="label">Reversal Reason:</span><span>{{ $payment->reversal->reason ?? '—' }}</span></div>
                            <div class="row"><span class="label">Reversed By:</span><span>{{ $payment->reversal->reversedBy->name ?? 'System' }}</span></div>
                            <div class="row"><span class="label">Reversed At:</span><span>{{ $payment->reversal->reversed_at?->format('d F Y H:i') }}</span></div>
                        @endif
                        <div class="line"></div>
                        <p class="center" style="font-size: 12px; color: #888;">Thank you for your payment.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-4 py-3 border-t border-neutral-200 dark:border-dark-border no-print">
                    <button type="button" data-close-modal="{{ $payment->id }}" class="px-3 py-1.5 text-sm text-neutral-600 dark:text-neutral-400 hover:underline">Close</button>
                    <button type="button" data-print-receipt="{{ $payment->id }}" class="px-3 py-1.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg">Print Receipt</button>
                </div>
            </div>
        </div>
    @endforeach

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var receiptCSS = '.center { text-align: center; } .line { border-top: 1px solid #e5e5e5; margin: 16px 0; } .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; } .label { color: #666; } .sub { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 2px; } .amount { font-size: 28px; font-weight: 700; color: #047857; } .print-btn { margin: 24px auto; display: block; padding: 10px 24px; background: #047857; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; } .receipt-watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 300px; max-width: 70%; height: auto; opacity: 0.06; pointer-events: none; z-index: 0; } .receipt-body { position: relative; z-index: 1; } @media print { .no-print { display: none !important; } .receipt-watermark { opacity: 0.05; } }';
            var receiptStyle = document.createElement('style');
            receiptStyle.textContent = receiptCSS;
            document.head.appendChild(receiptStyle);

            function openReceipt(id) {
                var modal = document.getElementById('receipt-modal-' + id);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }

            function closeReceipt(id) {
                var modal = document.getElementById('receipt-modal-' + id);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            document.querySelectorAll('[data-view-receipt]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openReceipt(this.getAttribute('data-view-receipt'));
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    closeReceipt(this.getAttribute('data-close-modal'));
                });
            });

            document.querySelectorAll('[data-print-receipt]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.getAttribute('data-print-receipt');
                    var body = document.getElementById('receipt-body-' + id);
                    if (!body) { return; }
                    var printWindow = window.open('', '_blank', 'width=800,height=600');
                    printWindow.document.write(
                        '<!DOCTYPE html><html><head><title>Receipt</title>' +
                        '<style>body { font-family: "Segoe UI", system-ui, sans-serif; color: #1a1a1a; max-width: 480px; margin: 40px auto; padding: 0 20px; } img { max-width: 100%; height: auto; }' +
                        receiptCSS + '</style>' +
                        '</head><body>' + body.innerHTML +
                        '<button class="print-btn no-print" onclick="window.print()">Print Receipt</button>' +
                        '</body></html>'
                    );
                    printWindow.document.close();
                    printWindow.focus();
                    printWindow.print();
                });
            });

            /* Reversal Modal handling */
            var reversalModal = document.getElementById('reversal-modal');
            var reversalPaymentId = document.getElementById('reversal_payment_id');
            var reversalReceipt = document.getElementById('reversal_receipt');
            var reversalAmount = document.getElementById('reversal_amount');

            window.openReversalModal = function(paymentId, receiptNumber, amount) {
                reversalPaymentId.value = paymentId;
                reversalReceipt.textContent = 'Receipt: ' + receiptNumber;
                reversalAmount.textContent = 'Amount: ₦' + Number(amount).toLocaleString(undefined, {minimumFractionDigits: 2});
                reversalModal.classList.remove('hidden');
                reversalModal.classList.add('flex');
                reversalModal.querySelector('textarea[name="reason"]').focus();
            };

            window.closeReversalModal = function() {
                reversalModal.classList.add('hidden');
                reversalModal.classList.remove('flex');
            };

            document.addEventListener('click', function(e) {
                if (e.target === reversalModal) {
                    closeReversalModal();
                }
            });

            /* After a payment is recorded, open its receipt immediately */
            var showReceipt = @json(session('show_receipt'));
            if (showReceipt) {
                var modal = document.getElementById('receipt-modal-' + showReceipt);
                if (modal) { openReceipt(showReceipt); }
            }
        });
    </script>
    @endpush
</x-layouts.app>