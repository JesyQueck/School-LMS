<x-layouts.app title="Import Students">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.students') }}">Students</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Import Students</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-2">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Import Students</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Bulk import students via CSV or Excel spreadsheet.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.import.template', ['format' => 'csv']) }}" class="inline-flex items-center gap-2 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template (CSV)
                </a>
                <a href="{{ route('admin.students.import.template', ['format' => 'xlsx']) }}" class="inline-flex items-center gap-2 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template (XLSX)
                </a>
            </div>
        </div>
    </div>

    @if(session('status'))
        <x-ui.alert type="success" class="mb-6">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="error" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    @if(session('import_stats'))
        @php
            $stats = session('import_stats');
            $credentials = collect($stats['credentials'] ?? []);
        @endphp

        <x-ui.card class="mb-6">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Import Complete — Login Credentials</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Temporary passwords for newly created accounts. Share these securely with users. They must change their password on first login.</p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Students without email: login with their admission number as email (format: <code>admission_no@{{ config('school.email_domain', 'school.local') }}</code>) and surname as password.</p>
            </div>
            <div class="p-6">
                @if($credentials->isNotEmpty())
                    <div class="mb-4">
                        <a href="{{ route('admin.students.import.credentials') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors text-sm">
                            Download Credentials CSV
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                            <thead class="bg-neutral-50 dark:bg-dark-surface">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Role</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Temporary Password</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Related Student</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                @foreach($credentials as $cred)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                        <td class="px-4 py-2 text-sm">{{ $cred['role'] }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ $cred['name'] }}</td>
                                        <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400 font-mono">{{ $cred['email'] }}</td>
                                        <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400 font-mono">{{ $cred['password'] }}</td>
                                        <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $cred['related_to'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">All accounts were reused from existing parents — no new credentials were generated.</p>
                @endif
            </div>
        </x-ui.card>
    @endif

    @if(session('preview'))
        @php
            $preview = session('student_import_preview');
            $invalidRows = collect($preview['invalid_rows'] ?? []);
            $validRows = collect($preview['valid_rows'] ?? []);
            $totalRows = $preview['total_rows'] ?? 0;
            $validCount = $totalRows - $invalidRows->count();
            $parentsToCreate = $validRows->sum(function ($row) {
                return isset($row['data']['parent_name']) && !empty($row['data']['parent_name']) ? 1 : 0;
            });
        @endphp

        <div class="space-y-6">
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Import Preview</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Review the data before importing students.</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-neutral-50 dark:bg-dark-surface rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $totalRows }}</div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">Total Rows</div>
                        </div>
                        <div class="bg-success-50 dark:bg-success-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-success-700 dark:text-success-300">{{ $validCount }}</div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">Valid Rows</div>
                        </div>
                        <div class="bg-danger-50 dark:bg-danger-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-danger-700 dark:text-danger-300">{{ $invalidRows->count() }}</div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">Invalid Rows</div>
                        </div>
                        <div class="bg-warning-50 dark:bg-warning-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-warning-700 dark:text-warning-300">{{ $parentsToCreate }}</div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">Parents to Create</div>
                        </div>
                    </div>

                    @if($validRows->isNotEmpty())
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Valid Rows (Ready to Import)</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Row</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Admission No</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Student Name</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Class</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Parent</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                        @foreach($validRows as $validRow)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-neutral-900 dark:text-white">{{ $validRow['row_number'] }}</td>
                                                <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $validRow['data']['admission_no'] ?? '' }}</td>
                                                <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ ($validRow['data']['first_name'] ?? '') . ' ' . ($validRow['data']['last_name'] ?? '') }}</td>
                                                <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $validRow['data']['class'] ?? '' }}</td>
                                                <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $validRow['data']['parent_name'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($invalidRows->isNotEmpty())
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Errors Found</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                                    <thead class="bg-neutral-50 dark:bg-dark-surface">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Row</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Field</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Problem</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                                        @foreach($invalidRows as $invalidRow)
                                            @foreach($invalidRow['errors'] as $error)
                                                <tr class="bg-danger-50 dark:bg-danger-900/20">
                                                    <td class="px-4 py-2 text-sm text-neutral-900 dark:text-white">{{ $invalidRow['row_number'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-danger-700 dark:text-danger-300 font-medium">{{ $error['field'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-danger-700 dark:text-danger-300">{{ $error['error'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $error['value'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('admin.students.import.errors') }}" class="inline-flex items-center gap-2 bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                    Download Errors CSV
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($validCount > 0)
                        <div class="flex gap-3 justify-end">
                            <form method="POST" action="{{ route('admin.students.import.cancel') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</button>
                            </form>
                            <form method="POST" action="{{ route('admin.students.import.confirm') }}">
                                @csrf
                                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Import {{ $validCount }} Students</button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-neutral-500 dark:text-neutral-400">No valid rows to import.</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    @else
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Upload Spreadsheet</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Supported formats: CSV, XLSX. Maximum file size: 5MB.</p>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.students.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="import_file" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Spreadsheet File</label>
                        <input id="import_file" name="import_file" type="file" accept=".csv,.xlsx" required class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-900/30 file:text-primary-700 dark:file:text-primary-300 hover:file:bg-primary-100 dark:hover:file:bg-primary-900/40">
                        @error('import_file')
                            <p class="text-sm text-danger-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 justify-end">
                        <a href="{{ route('admin.students') }}" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</a>
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring:ring-offset-dark-bg">Upload and Validate</button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    @endif
</x-layouts.app>
