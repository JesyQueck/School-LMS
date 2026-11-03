<x-layouts.app title="Finance Management">
    <style>
        .stat-card-wrap .font-bold { word-break: break-all; line-height: 1.1; }
    </style>
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Finance</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Finance Management</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage fee types, view financial summaries, and export reports.</p>
    </div>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300">{{ session('status') }}</div>
            </div>
        </x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <div class="flex flex-col gap-2">
                <h4 class="text-xs font-bold text-danger-800 dark:text-danger-300 uppercase tracking-wider mb-1">Please fix the following errors:</h4>
                <ul class="list-disc list-inside text-sm text-danger-700 dark:text-danger-400 space-y-1">
                    <li>{{ $errors->first() }}</li>
                </ul>
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
    <x-ui.card class="mb-6" elevated>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Fees by Class</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Breakdown of expected, collected, and outstanding amounts per class.</p>
                </div>
            </div>
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
        <div class="lg:col-span-6">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Create Fee Type</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Define a new fee type. Student fees are auto-generated for all students in the selected class.</p>
                </div>
                <form method="POST" action="{{ route('admin.finance.fee-types.store') }}" class="p-5 flex-1 flex flex-col">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Fee Name <span class="text-danger-500">*</span></label>
                        <input name="name" type="text" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Amount (₦) <span class="text-danger-500">*</span></label>
                            <input name="amount" type="number" step="0.01" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Term <span class="text-danger-500">*</span></label>
                            <select name="term_id" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Class</label>
                        <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Due Date</label>
                        <input name="due_date" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Description</label>
                        <input name="description" type="text" placeholder="Optional note" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                    <div class="mt-auto">
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm transition-colors">Save Fee Type</button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-6">
            <x-ui.card class="h-full flex flex-col" padding="false" elevated>
                <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Fee Types</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">List of all configured fee types.</p>
                </div>
                <div class="p-5 flex-1 overflow-y-auto">
                    @if($feeTypes->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                                <thead class="bg-neutral-50 dark:bg-dark-surface">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Fee Type</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Term</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Amount</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                    @foreach($feeTypes as $feeType)
                                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                            <td class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $feeType->name }}</td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $feeType->term->name }}</td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $feeType->class->name ?? 'All Classes' }}</td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400 text-right">₦{{ number_format($feeType->amount, 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $feeType->due_date ? $feeType->due_date->format('d M Y') : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 py-3 border-t border-neutral-200 dark:border-dark-border mt-4">
                            {{ $feeTypes->links() }}
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-8">No fee types configured yet.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Financial Report Export --}}
    <x-ui.card class="mb-6" elevated>
        <div class="px-6 py-5 border-b-2 border-neutral-200 dark:border-dark-border">
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Export Financial Report</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Generate a detailed PDF report of fee collections by class and student.</p>
        </div>
        <form id="report-form" method="GET" action="{{ route('admin.finance.report.export') }}" class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-neutral-200 dark:border-dark-border">
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Term</label>
                <select name="term_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Class</label>
                <select name="class_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Academic Session</label>
                <select name="academic_session_id" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\AcademicSession::all() as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="p-5">
            <button type="button" onclick="document.getElementById('report-form').requestSubmit()" class="w-full sm:w-auto bg-success-600 hover:bg-success-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm text-sm">
                Download PDF Report
            </button>
        </div>
    </x-ui.card>
</x-layouts.app>