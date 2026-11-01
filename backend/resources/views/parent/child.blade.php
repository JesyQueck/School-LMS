<x-layouts.app title="{{ $student->full_name }} - Profile">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="{{ route('parent.dashboard') }}">Parent</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('parent.children.index') }}">Children</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>{{ $student->full_name }}</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <div class="flex items-center gap-4 mt-4">
            @if($student->user && $student->user->profile_photo)
                <img src="{{ asset('storage/' . $student->user->profile_photo) }}" alt="{{ $student->full_name }}" class="h-20 w-20 rounded-full object-cover">
            @else
                <div class="h-20 w-20 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-2xl">
                    {{ substr($student->full_name ?? $student->admission_no, 0, 1) }}
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $student->full_name }}</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $student->schoolClass->name ?? 'Unassigned' }} &middot; {{ $student->admission_no }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Information --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Personal Information</h3>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Full Name</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Date of Birth</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->date_of_birth ? $student->date_of_birth->format('j F Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Gender</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($student->gender ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Blood Group</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->blood_group ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Religion</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->religion ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">State of Origin</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->state_of_origin ?? 'N/A' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Home Address</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->home_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">City</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->city ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">State</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->state ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Nationality</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->nationality ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">LGA</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->lga ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">House</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->house ?? 'N/A' }}</p>
                    </div>
                </div>
            </x-ui.card>

            {{-- School Information --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">School Information</h3>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Admission No</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->admission_no }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Admission Date</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->admission_date ? $student->admission_date->format('j F Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Current Class</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->schoolClass->name ?? 'Unassigned' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Academic Session</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->academicSession->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Student Type</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($student->student_type ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Status</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($student->status ?? 'N/A') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Previous School</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->previous_school ?? 'N/A' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Previous School Address</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->previous_school_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Previous Class</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->previous_class ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Previous Year</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $student->previous_year_attended ?? 'N/A' }}</p>
                    </div>
                </div>
            </x-ui-card>

            {{-- Parent/Guardian Contacts --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Parent / Guardian Contacts</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($student->contacts->isNotEmpty())
                        @foreach($student->contacts as $contact)
                            <div class="flex items-start gap-3 p-4 rounded-lg border border-neutral-100 dark:border-dark-border">
                                <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium">
                                    {{ substr($contact->name ?? 'C', 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $contact->name }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $contact->relationship }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ $contact->phone }}</p>
                                    @if($contact->email)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ $contact->email }}</p>
                                    @endif
                                    @if($contact->is_primary)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 mt-1">Primary Contact</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No contacts recorded.</p>
                    @endif
                </div>
            </x-ui.card>

            {{-- Emergency Contacts --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Emergency Contacts</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($student->emergencyContacts->isNotEmpty())
                        @foreach($student->emergencyContacts as $contact)
                            <div class="flex items-start gap-3 p-4 rounded-lg border border-neutral-100 dark:border-dark-border bg-amber-50 dark:bg-amber-900/10">
                                <div class="h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 flex items-center justify-center font-medium">
                                    {{ substr($contact->name ?? 'E', 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $contact->name }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $contact->relationship }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ $contact->phone }}</p>
                                    @if($contact->email)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ $contact->email }}</p>
                                    @endif>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No emergency contacts recorded.</p>
                    @endif
                </div>
            </x-ui.card>

            {{-- Documents --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Documents</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($student->documents->isNotEmpty())
                        @foreach($student->documents as $doc)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-neutral-100 dark:border-dark-border">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $doc->document_type }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Uploaded {{ $doc->created_at->format('j M Y') }}</p>
                                    </div>
                                </div>
                                @if($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View</a>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No documents uploaded.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            {{-- Quick Actions --}}
            <x-ui.card>
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Quick Actions</h3>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('parent.children.results', $student) }}" class="block p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Academic Results</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">View grades and reports</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.children.attendance', $student) }}" class="block p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Attendance Records</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Daily attendance history</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.children.fees', $student) }}" class="block p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Fees & Payments</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Balances and receipts</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.children.report-cards', $student) }}" class="block p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Report Cards</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Download PDFs</p>
                        </div>
                    </a>
                    <a href="{{ route('parent.timetable') }}" class="block p-3 rounded-lg border border-neutral-200 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Timetable</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Weekly schedule</p>
                        </div>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>