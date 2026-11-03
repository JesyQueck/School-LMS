<x-layouts.app title="Student Fee Payments">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/bursar/dashboard">Bursar</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Student Fee Payments</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Student Fee Payments</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Record payments and view student fee obligations.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif

    {{-- Finance Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Total Expected" :value="'₦' . number_format($finance['expected'], 2)" icon="wallet" compact="true" />
        <x-ui.stat-card label="Amount Collected" :value="'₦' . number_format($finance['collected'], 2)" icon="wallet" compact="true" :trend="['direction' => 'up', 'value' => 'Collected']" />
        <x-ui.stat-card label="Still Owed" :value="'₦' . number_format($finance['outstanding'], 2)" icon="wallet" compact="true" :trend="['direction' => 'down', 'value' => 'Outstanding']" />
        <x-ui.stat-card label="Collection Rate" :value="$finance['collection_rate'] . '%'" icon="chart" compact="true" :trend="['direction' => $finance['collection_rate'] >= 70 ? 'up' : 'neutral', 'value' => 'of fees collected']" />
    </div>

    {{-- Per-Class Breakdown --}}
    <x-ui.card class="mb-6">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Fees by Class</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Breakdown of expected, collected, and outstanding amounts per class.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                <thead class="bg-neutral-50 dark:bg-dark-surface">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Students</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Expected</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Collected</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Still Owed</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                    @forelse($classSummary as $cs)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-sm font-medium text-neutral-900 dark:text-white">{{ $cs['class'] }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $cs['total'] }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['expected'], 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-success-600 dark:text-success-400 text-right">₦{{ number_format($cs['collected'], 2) }}</td>
                            <td class="px-6 py-3.5 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($cs['outstanding'], 2) }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <span class="inline-flex items-center rounded-full {{ $cs['collection_rate'] >= 70 ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : ($cs['collection_rate'] >= 40 ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300' : 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300') }} px-2 py-0.5 text-xs font-medium">
                                    {{ $cs['collection_rate'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No classes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-stretch">
        <div class="lg:col-span-7">
            {{-- Student Fees --}}
            <x-ui.card class="h-full flex flex-col" elevated>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Student Fees</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">View and filter individual student fee obligations.</p>
                </div>
                <form method="GET" class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 border-b border-neutral-200 dark:border-dark-border">
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
                        <a href="{{ route('bursar.student-fees') }}" class="text-sm text-neutral-500 dark:text-neutral-400 hover:underline">Reset</a>
                    </div>
                </form>
                <div class="overflow-x-auto mt-3 flex-1">
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
                                <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Actions</th>
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
                                        <a href="{{ route('bursar.student-fees.show', $fee) }}" class="hover:text-primary-600 dark:hover:text-primary-400 hover:underline">{{ $fee->student->full_name ?? 'N/A' }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->student->schoolClass->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $fee->feeType->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">₦{{ number_format($fee->amount_expected, 2) }}</td>
                                    <td class="px-3 py-2 text-sm text-success-600 dark:text-success-400">₦{{ number_format($paid, 2) }}</td>
                                    <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">₦{{ number_format($balance, 2) }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} px-2 py-0.5 text-xs font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-sm">
                                        @if($fee->computed_status !== 'paid')
                                            <button type="button"
                                                onclick="openPaymentModal({{ $fee->id }}, '{{ addslashes($fee->student->full_name) }}', '{{ $fee->feeType->name }}', {{ $fee->amount_expected }}, {{ $balance }})"
                                                class="px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">
                                                Record Payment
                                            </button>
                                        @else
                                            <span class="text-xs text-neutral-400 dark:text-neutral-500">Fully Paid</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-3 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No student fees found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-neutral-200 dark:border-dark-border">
                    {{ $studentFees->links() }}
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-5">
            {{-- Record Payment --}}
            <x-ui.card class="h-full flex flex-col" elevated>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Record Payment</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Select a student fee obligation and enter the payment details.</p>
                </div>
                <form method="POST" action="{{ route('bursar.payments.store') }}" class="p-5 flex-1 flex flex-col">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Student Name / Admission No <span class="text-danger-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="student_search" placeholder="Type student name or admission no..."
                                class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
                                autocomplete="off">
                            <input type="hidden" name="student_fee_id" id="selected_fee_id">
                            <div id="student_fee_dropdown" class="absolute z-10 w-full mt-1 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border rounded-lg shadow-lg max-h-48 overflow-y-auto hidden"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Amount Paid (₦) <span class="text-danger-500">*</span></label>
                        <input name="amount_paid" type="number" step="0.01" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Payment Date <span class="text-danger-500">*</span></label>
                        <input name="payment_date" type="date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Reference (optional)</label>
                        <input name="reference" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"></textarea>
                    </div>
                    <div class="mt-auto">
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Record Payment</button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>

    {{-- Payment Modal (for inline payment recording) --}}
    <div id="payment-modal" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-50 overflow-y-auto p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md flex flex-col" style="font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <form method="POST" action="{{ route('bursar.payments.store') }}" class="flex-1 flex flex-col">
                @csrf
                <div class="px-4 py-3 border-b">
                    <h2 class="text-lg font-semibold" style="color: #000000;">Record Payment</h2>
                </div>
                <div class="p-4 overflow-y-auto space-y-4">
                    <input type="hidden" name="student_fee_id" id="modal_fee_id">
                    <div>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white" id="modal_student_name"></p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400" id="modal_fee_info"></p>
                    </div>
                    <div class="border-t border-neutral-200 dark:border-dark-border pt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Outstanding Balance</label>
                            <p class="text-2xl font-bold text-danger-600 dark:text-danger-400" id="modal_outstanding">₦0.00</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Amount Paid (₦) <span class="text-danger-500">*</span></label>
                            <input name="amount_paid" type="number" step="0.01" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" id="modal_amount_paid">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Payment Method</label>
                            <select name="payment_method" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Payment Date <span class="text-danger-500">*</span></label>
                            <input name="payment_date" type="date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Reference (optional)</label>
                            <input name="reference" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Notes (optional)</label>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"></textarea>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-t border-neutral-200 dark:border-dark-border flex gap-2">
                    <button type="button" onclick="closePaymentModal()" class="flex-1 px-3 py-1.5 text-sm text-neutral-600 dark:text-neutral-400 hover:underline">Cancel</button>
                    <button type="submit" class="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var baseUrl = '{{ route("bursar.student-fees") }}';
            var searchInput = document.querySelector('input[name="student_search"]');
            var dropdown = document.getElementById('student_fee_dropdown');
            var hiddenInput = document.getElementById('selected_fee_id');

            if (searchInput) {
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

                var mainForm = searchInput ? searchInput.closest('form') : null;
                if (mainForm) {
                    mainForm.addEventListener('submit', function(e) {
                        if (!hiddenInput.value) {
                            e.preventDefault();
                            searchInput.focus();
                            alert('Please select a student from the search results.');
                        }
                    });
                }

            /* Payment Modal handling */
            var paymentModal = document.getElementById('payment-modal');
            var modalFeeId = document.getElementById('modal_fee_id');
            var modalStudentName = document.getElementById('modal_student_name');
            var modalFeeInfo = document.getElementById('modal_fee_info');
            var modalOutstanding = document.getElementById('modal_outstanding');
            var modalAmountPaid = document.getElementById('modal_amount_paid');

            window.openPaymentModal = function(feeId, studentName, feeTypeName, amountExpected, balance) {
                modalFeeId.value = feeId;
                modalStudentName.textContent = studentName;
                modalFeeInfo.textContent = feeTypeName + ' (Expected: ₦' + Number(amountExpected).toLocaleString(undefined, {minimumFractionDigits: 2}) + ')';
                modalOutstanding.textContent = '₦' + Number(balance).toLocaleString(undefined, {minimumFractionDigits: 2});
                modalAmountPaid.value = balance;
                modalAmountPaid.max = balance;
                paymentModal.classList.remove('hidden');
                paymentModal.classList.add('flex');
                modalAmountPaid.focus();
            };

            window.closePaymentModal = function() {
                paymentModal.classList.add('hidden');
                paymentModal.classList.remove('flex');
            };

            document.addEventListener('click', function(e) {
                if (e.target === paymentModal) {
                    closePaymentModal();
                }
            });

            /* Receipt modal handling */
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