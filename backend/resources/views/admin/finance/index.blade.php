<x-layouts.app title="Finance Management">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard" active>Finance</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Finance Management</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage fee types, student fees, and payment records.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">{{ $errors->first() }}</x-ui.alert>
    @endif

    {{-- Finance Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <x-ui.stat-card label="Expected Fees" :value="'₦' . number_format($finance['expected'], 2)" icon="wallet" compact />
        <x-ui.stat-card label="Collected" :value="'₦' . number_format($finance['collected'], 2)" icon="wallet" compact />
        <x-ui.stat-card label="Outstanding" :value="'₦' . number_format($finance['outstanding'], 2)" icon="wallet" compact />
        <x-ui.stat-card label="Collection Rate" :value="$finance['collection_rate'] . '%'" icon="wallet" compact />
    </div>

    {{-- Per-Class Breakdown --}}
    <x-ui.card class="mb-6">
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Per-Class Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Students</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Expected</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Collected</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Outstanding</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($classSummary as $cs)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $cs['class'] }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $cs['total'] }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['expected'], 2) }}</td>
                            <td class="px-3 py-2 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($cs['collected'], 2) }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['outstanding'], 2) }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400 text-right">{{ $cs['collection_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-sm text-neutral-500 dark:text-neutral-400">No classes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-stretch">
        <div class="lg:col-span-6">
            <x-ui.card class="h-full flex flex-col" padding="false">
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Create Fee Type</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Define a new fee type. Student fees are auto-generated for all students in the selected class.</p>
                </div>
                <form method="POST" action="{{ route('admin.finance.fee-types.store') }}" class="p-3 flex-1 flex flex-col">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Fee Name <span class="text-danger-500">*</span></label>
                        <input name="name" type="text" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Amount (₦) <span class="text-danger-500">*</span></label>
                            <input name="amount" type="number" step="0.01" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Term <span class="text-danger-500">*</span></label>
                            <select name="term_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Class</label>
                        <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Due Date</label>
                        <input name="due_date" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                        <input name="description" type="text" placeholder="Optional note" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                    </div>
                    <div class="mt-auto">
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-1.5 rounded-lg shadow-sm text-sm">Save Fee Type</button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-6">
            <x-ui.card class="h-full flex flex-col" padding="false">
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Record Payment</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Select a student fee obligation and enter the payment details.</p>
                </div>
                <form method="POST" action="{{ route('admin.finance.payments.store') }}" class="p-3 flex-1 flex flex-col">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Student Identity <span class="text-danger-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="student_search" placeholder="Type student name or admission no..."
                                class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm focus:ring-1 focus:ring-primary-500"
                                autocomplete="off">
                            <input type="hidden" name="student_fee_id" id="selected_fee_id">
                            <div id="student_fee_dropdown" class="absolute z-10 w-full mt-1 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border rounded-lg shadow-lg max-h-48 overflow-y-auto hidden"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Amount Paid (₦) <span class="text-danger-500">*</span></label>
                        <input name="amount_paid" type="number" step="0.01" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Payment Date <span class="text-danger-500">*</span></label>
                        <input name="payment_date" type="date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Reference</label>
                        <input name="reference" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-1.5 text-sm">
                    </div>
                    <div class="mt-auto">
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-1.5 rounded-lg shadow-sm text-sm">Record Payment</button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>

    {{-- Student Fees --}}
    <x-ui.card class="mb-6">
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Student Fees</h3>
        </div>
         <form method="GET" class="p-4 grid grid-cols-2 sm:grid-cols-5 gap-3 border-b border-neutral-200 dark:border-dark-border">
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Class</label>
                <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ ($filters['class_id'] ?? '') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Term</label>
                <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ ($filters['term_id'] ?? '') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    <option value="paid" {{ ($filters['status'] ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ ($filters['status'] ?? '') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="unpaid" {{ ($filters['status'] ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Search</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name / ADM" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm">Filter</button>
                <a href="{{ route('admin.finance') }}" class="text-sm text-neutral-500 dark:text-neutral-400 hover:underline">Reset</a>
            </div>
        </form>
        <div class="overflow-x-auto mt-3">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student</th>
                         <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                         <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee</th>
                         <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Total</th>
                         <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Paid</th>
                         <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Balance</th>
                         <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($studentFees as $fee)
                        @php
                            $paid = $fee->payments->sum('amount_paid');
                            $balance = max(0, ($fee->amount_expected ?? 0) - $paid);
                            $statusConfig = match($fee->computed_status ?? 'unpaid') {
                                'paid' => ['bg' => 'bg-success-100 dark:bg-success-900/30', 'text' => 'text-success-700 dark:text-success-300', 'label' => 'Paid'],
                                'partial' => ['bg' => 'bg-warning-100 dark:bg-warning-900/30', 'text' => 'text-warning-700 dark:text-warning-300', 'label' => 'Partial'],
                                default => ['bg' => 'bg-danger-100 dark:bg-danger-900/30', 'text' => 'text-danger-700 dark:text-danger-300', 'label' => 'Unpaid'],
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white">
                                <a href="{{ route('admin.finance.student-fees.show', $fee) }}" class="hover:text-primary-600 dark:hover:text-primary-400 hover:underline">{{ $fee->student->full_name ?? 'N/A' }}</a>
                            </td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->student->schoolClass->name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->feeType->name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">₦{{ number_format($fee->amount_expected, 2) }}</td>
                            <td class="px-3 py-2 text-sm text-success-600 dark:text-success-400">₦{{ number_format($paid, 2) }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">₦{{ number_format($balance, 2) }}</td>
                            <td class="px-3 py-2 text-sm">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No student fees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $studentFees->links() }}
        </div>
    </x-ui.card>

    {{-- Payments --}}
    <x-ui.card>
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">Payments</h3>
        </div>
        <div class="px-4 pb-3">
            <div class="flex flex-col sm:flex-row sm:items-end gap-2 mt-3">
                <form id="report-form" method="GET" action="{{ route('admin.finance.report.export') }}" class="grid grid-cols-2 sm:grid-cols-3 gap-2 flex-1">
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Term</label>
                        <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                            <option value="">All</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Class</label>
                        <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                            <option value="">All</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <button type="button" onclick="document.getElementById('report-form').requestSubmit()" class="w-full sm:w-auto bg-success-600 hover:bg-success-700 text-white font-medium px-3 py-1.5 rounded-lg shadow-sm text-sm">
                    Download Report
                </button>
            </div>
        </div>
        <div class="overflow-x-auto mt-3">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Receipt</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Amount</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Method</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $payment->receipt_number }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->studentFee->student->full_name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->studentFee->feeType->name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-success-600 dark:text-success-400">₦{{ number_format($payment->amount_paid, 2) }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->payment_date?->format('d M Y') }}</td>
                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->recordedBy->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $payments->links() }}
        </div>
    </x-ui.card>

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var searchInput = document.querySelector('input[name="student_search"]');
            if (!searchInput) return;

            var dropdown = document.getElementById('student_fee_dropdown');
            var hiddenInput = document.getElementById('selected_fee_id');
            var baseUrl = '{{ route("admin.finance") }}';

            searchInput.addEventListener('input', function() {
                var query = this.value.trim();
                if (!query) {
                    dropdown.classList.add('hidden');
                    dropdown.innerHTML = '';
                    return;
                }
                fetch(baseUrl + '/search-students?q=' + encodeURIComponent(query))
                        .then(function(r) { return r.json(); })
                        .then(function(results) {
                            dropdown.innerHTML = '';
                            if (results.length === 0) {
                                dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-neutral-500">No matching fees found</div>';
                            } else {
                                results.forEach(function(item) {
                                    var div = document.createElement('div');
                                    div.className = 'px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-800';
                                    div.innerHTML = '<div class="font-medium text-sm text-neutral-900 dark:text-white">' + item.student_name + '</div>' +
                                        '<div class="text-xs text-neutral-500 dark:text-neutral-400">' + item.fee_type + ' - ' + item.class + ' (₦' + item.amount_expected + ')</div>';
                                    div.onclick = function() {
                                        searchInput.value = item.label;
                                        hiddenInput.value = item.id;
                                        dropdown.classList.add('hidden');
                                    };
                                    dropdown.appendChild(div);
                                });
                            }
                            dropdown.classList.remove('hidden');
                        })
                        .catch(function() {
                            dropdown.classList.add('hidden');
                        });
            });

            document.addEventListener('click', function(e) {
                if (e.target !== searchInput && !searchInput.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            searchInput.closest('form').addEventListener('submit', function(e) {
                if (!hiddenInput.value) {
                    e.preventDefault();
                    searchInput.focus();
                    alert('Please select a student from the search results.');
                }
            });
        });
        </script>
    @endpush</x-layouts.app>
