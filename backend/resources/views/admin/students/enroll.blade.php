<x-layouts.app title="Enroll Student">
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumb-item href="/admin/dashboard">Admin</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item href="{{ route('admin.students') }}">Students</x-ui.breadcrumb-item>
            <x-ui.breadcrumb-item active>Enroll Student</x-ui.breadcrumb-item>
        </x-ui.breadcrumbs>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-2">Enroll Student</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Enroll a new student with complete information including parent/guardian, emergency contacts, and documents.</p>
    </div>

    <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- 1. Student Information -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">1. Student Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.input name="first_name" label="First Name" placeholder="e.g. John" required error="{{ $errors->first('first_name') }}" value="{{ old('first_name') }}" />
                    <x-ui.input name="middle_name" label="Middle Name" placeholder="e.g. Michael" error="{{ $errors->first('middle_name') }}" value="{{ old('middle_name') }}" />
                    <x-ui.input name="last_name" label="Last Name" placeholder="e.g. Doe" required error="{{ $errors->first('last_name') }}" value="{{ old('last_name') }}" />

                    <x-ui.input name="admission_no" label="Admission Number" placeholder="e.g. GRA-2024-001" required error="{{ $errors->first('admission_no') }}" value="{{ old('admission_no') }}" helper="Manually entered; must be unique." />
                    <x-ui.input name="date_of_birth" label="Date of Birth" type="date" required error="{{ $errors->first('date_of_birth') }}" value="{{ old('date_of_birth') }}" />
                    <x-ui.select name="gender" label="Gender" required error="{{ $errors->first('gender') }}">
                        <option value="">Select gender</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </x-ui.select>

                    <x-ui.input name="nationality" label="Nationality" placeholder="e.g. Nigerian" error="{{ $errors->first('nationality') }}" value="{{ old('nationality') }}" />
                    <x-ui.input name="state_of_origin" label="State of Origin" placeholder="e.g. Lagos" error="{{ $errors->first('state_of_origin') }}" value="{{ old('state_of_origin') }}" />
                    <x-ui.input name="lga" label="LGA" placeholder="e.g. Ikeja" error="{{ $errors->first('lga') }}" value="{{ old('lga') }}" />
                    <x-ui.input name="religion" label="Religion" placeholder="e.g. Christianity" error="{{ $errors->first('religion') }}" value="{{ old('religion') }}" />

                    <x-ui.input name="phone" label="Student Phone" type="tel" placeholder="e.g. 08012345678" error="{{ $errors->first('phone') }}" value="{{ old('phone') }}" />
                    <x-ui.input name="email" label="Student Email (Optional)" type="email" placeholder="e.g. student@example.com" error="{{ $errors->first('email') }}" value="{{ old('email') }}" />

                    <div class="md:col-span-3">
                        <x-ui.textarea name="home_address" label="Home Address" placeholder="e.g. 123 Main Street, Lagos" error="{{ $errors->first('home_address') }}" rows="2">{{ old('home_address') }}</x-ui.textarea>
                    </div>
                    <x-ui.input name="city" label="City" placeholder="e.g. Lagos" error="{{ $errors->first('city') }}" value="{{ old('city') }}" />
                    <x-ui.input name="state" label="State" placeholder="e.g. Lagos" error="{{ $errors->first('state') }}" value="{{ old('state') }}" />
                </div>
            </div>
        </x-ui.card>

        <!-- 2. Admission & Academic Information -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">2. Admission &amp; Academic Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input name="admission_date" label="Admission Date" type="date" required error="{{ $errors->first('admission_date') }}" value="{{ old('admission_date') }}" />
                    <x-ui.select name="academic_session_id" label="Academic Session" required error="{{ $errors->first('academic_session_id') }}">
                        <option value="">Select session</option>
                        @foreach($academicSessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="class_id" label="Class" required error="{{ $errors->first('class_id') }}">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="student_type" label="Student Type" required error="{{ $errors->first('student_type') }}">
                        <option value="new" {{ old('student_type', 'new') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="returning" {{ old('student_type') == 'returning' ? 'selected' : '' }}>Returning</option>
                        <option value="transfer" {{ old('student_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </x-ui.select>

                    <x-ui.input name="previous_school" label="Previous School" placeholder="e.g. ABC Primary School" error="{{ $errors->first('previous_school') }}" value="{{ old('previous_school') }}" />
                    <x-ui.input name="previous_year_attended" label="Year Previously Attended" placeholder="e.g. 2024" error="{{ $errors->first('previous_year_attended') }}" value="{{ old('previous_year_attended') }}" />
                    <x-ui.input name="previous_class" label="Previous Class / Grade" placeholder="e.g. Primary 5" error="{{ $errors->first('previous_class') }}" value="{{ old('previous_class') }}" />

                    <div class="md:col-span-2">
                        <x-ui.textarea name="previous_school_address" label="Previous School Address" placeholder="e.g. 456 School Road, Lagos" error="{{ $errors->first('previous_school_address') }}" rows="2">{{ old('previous_school_address') }}</x-ui.textarea>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- 3. Parent / Guardian -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">3. Parent / Guardian</h2>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 mb-4">Primary Guardian</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <x-ui.input name="parent_name" label="Full Name" placeholder="e.g. Mr. John Doe" required error="{{ $errors->first('parent_name') }}" value="{{ old('parent_name') }}" />
                    <x-ui.input name="parent_email" label="Email" type="email" placeholder="e.g. parent@example.com" required error="{{ $errors->first('parent_email') }}" value="{{ old('parent_email') }}" />

                    <x-ui.input name="parent_phone" label="Phone" type="tel" placeholder="e.g. 08012345678" required error="{{ $errors->first('parent_phone') }}" value="{{ old('parent_phone') }}" />
                    <x-ui.input name="parent_whatsapp" label="WhatsApp" type="tel" placeholder="e.g. 08012345678" error="{{ $errors->first('parent_whatsapp') }}" value="{{ old('parent_whatsapp') }}" />

                    <x-ui.select name="parent_relationship" label="Relationship to Student" error="{{ $errors->first('parent_relationship') }}">
                        <option value="">Select relationship</option>
                        <option value="father" {{ old('parent_relationship') == 'father' ? 'selected' : '' }}>Father</option>
                        <option value="mother" {{ old('parent_relationship') == 'mother' ? 'selected' : '' }}>Mother</option>
                        <option value="guardian" {{ old('parent_relationship') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                        <option value="other" {{ old('parent_relationship') == 'other' ? 'selected' : '' }}>Other</option>
                    </x-ui.select>
                    <x-ui.input name="parent_occupation" label="Occupation" placeholder="e.g. Engineer" error="{{ $errors->first('parent_occupation') }}" value="{{ old('parent_occupation') }}" />

                    <div class="md:col-span-2">
                        <x-ui.textarea name="parent_address" label="Address" placeholder="e.g. 123 Main Street, Lagos" error="{{ $errors->first('parent_address') }}" rows="2">{{ old('parent_address') }}</x-ui.textarea>
                    </div>
                    <x-ui.input name="parent_city" label="City" placeholder="e.g. Lagos" error="{{ $errors->first('parent_city') }}" value="{{ old('parent_city') }}" />
                    <x-ui.input name="parent_state" label="State" placeholder="e.g. Lagos" error="{{ $errors->first('parent_state') }}" value="{{ old('parent_state') }}" />
                </div>

                <h3 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 mb-4">Father's Information (Optional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <x-ui.input name="father_name" label="Full Name" placeholder="e.g. Mr. John Doe" error="{{ $errors->first('father_name') }}" value="{{ old('father_name') }}" />
                    <x-ui.input name="father_phone" label="Phone" type="tel" error="{{ $errors->first('father_phone') }}" value="{{ old('father_phone') }}" />

                    <x-ui.input name="father_whatsapp" label="WhatsApp" type="tel" error="{{ $errors->first('father_whatsapp') }}" value="{{ old('father_whatsapp') }}" />
                    <x-ui.input name="father_email" label="Email" type="email" error="{{ $errors->first('father_email') }}" value="{{ old('father_email') }}" />
                    <div class="md:col-span-2">
                        <x-ui.input name="father_occupation" label="Occupation" error="{{ $errors->first('father_occupation') }}" value="{{ old('father_occupation') }}" />
                    </div>
                </div>

                <h3 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 mb-4">Mother's Information (Optional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input name="mother_name" label="Full Name" placeholder="e.g. Mrs. Jane Doe" error="{{ $errors->first('mother_name') }}" value="{{ old('mother_name') }}" />
                    <x-ui.input name="mother_phone" label="Phone" type="tel" error="{{ $errors->first('mother_phone') }}" value="{{ old('mother_phone') }}" />

                    <x-ui.input name="mother_whatsapp" label="WhatsApp" type="tel" error="{{ $errors->first('mother_whatsapp') }}" value="{{ old('mother_whatsapp') }}" />
                    <x-ui.input name="mother_email" label="Email" type="email" error="{{ $errors->first('mother_email') }}" value="{{ old('mother_email') }}" />
                    <div class="md:col-span-2">
                        <x-ui.input name="mother_occupation" label="Occupation" error="{{ $errors->first('mother_occupation') }}" value="{{ old('mother_occupation') }}" />
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- 4. Emergency Contact -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">4. Emergency Contact</h2>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 mb-4">Primary Emergency Contact</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <x-ui.input name="emergency_1_name" label="Full Name" placeholder="e.g. Mrs. Sarah Johnson" required error="{{ $errors->first('emergency_1_name') }}" value="{{ old('emergency_1_name') }}" />
                    <x-ui.input name="emergency_1_relationship" label="Relationship" placeholder="e.g. Aunt" required error="{{ $errors->first('emergency_1_relationship') }}" value="{{ old('emergency_1_relationship') }}" />

                    <x-ui.input name="emergency_1_phone" label="Phone" type="tel" placeholder="e.g. 08012345678" required error="{{ $errors->first('emergency_1_phone') }}" value="{{ old('emergency_1_phone') }}" />
                    <x-ui.input name="emergency_1_whatsapp" label="WhatsApp" type="tel" error="{{ $errors->first('emergency_1_whatsapp') }}" value="{{ old('emergency_1_whatsapp') }}" />

                    <div class="md:col-span-2">
                        <x-ui.textarea name="emergency_1_address" label="Address" placeholder="e.g. 789 Oak Street, Lagos" error="{{ $errors->first('emergency_1_address') }}" rows="2">{{ old('emergency_1_address') }}</x-ui.textarea>
                    </div>
                </div>

                <h3 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 mb-4">Secondary Emergency Contact (Optional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input name="emergency_2_name" label="Full Name" placeholder="e.g. Mr. Robert Smith" error="{{ $errors->first('emergency_2_name') }}" value="{{ old('emergency_2_name') }}" />
                    <x-ui.input name="emergency_2_relationship" label="Relationship" error="{{ $errors->first('emergency_2_relationship') }}" value="{{ old('emergency_2_relationship') }}" />

                    <x-ui.input name="emergency_2_phone" label="Phone" type="tel" error="{{ $errors->first('emergency_2_phone') }}" value="{{ old('emergency_2_phone') }}" />
                    <div class="md:col-span-2">
                        <x-ui.textarea name="emergency_2_address" label="Address" placeholder="e.g. 321 Pine Road, Lagos" error="{{ $errors->first('emergency_2_address') }}" rows="2">{{ old('emergency_2_address') }}</x-ui.textarea>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- 5. Documents -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">5. Documents (Optional)</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input name="document_passport" label="Passport Photograph" type="file" accept="image/*" error="{{ $errors->first('document_passport') }}" />
                    <x-ui.input name="document_birth_certificate" label="Birth Certificate" type="file" accept=".pdf,.jpg,.jpeg,.png" error="{{ $errors->first('document_birth_certificate') }}" />

                    <x-ui.input name="document_previous_result" label="Previous School Result / Report" type="file" accept=".pdf,.jpg,.jpeg,.png" error="{{ $errors->first('document_previous_result') }}" />
                    <x-ui.input name="document_transfer_certificate" label="Transfer Certificate" type="file" accept=".pdf,.jpg,.jpeg,.png" error="{{ $errors->first('document_transfer_certificate') }}" />

                    <x-ui.input name="document_identification" label="Identification Document" type="file" accept=".pdf,.jpg,.jpeg,.png" error="{{ $errors->first('document_identification') }}" />
                    <x-ui.input name="document_other" label="Other Admission Document" type="file" accept=".pdf,.jpg,.jpeg,.png" error="{{ $errors->first('document_other') }}" />
                </div>
            </div>
        </x-ui.card>

        <!-- 6. Account Setup -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">6. Account Setup</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input name="password" label="Temporary Password" type="password" placeholder="Leave blank to auto-generate" error="{{ $errors->first('password') }}" helper="Minimum 8 characters with mixed case, a number, and a symbol." />

                    <x-ui.input name="password_confirmation" label="Confirm Password" type="password" placeholder="Confirm temporary password" />
                </div>
            </div>
        </x-ui.card>

        <!-- 7. Review / Submit -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-dark-border">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">7. Review &amp; Submit</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">Please review all information before submitting. The student will be enrolled with these details.</p>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('admin.students') }}" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancel</a>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-2 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Enroll Student</button>
                </div>
            </div>
        </x-ui.card>
    </form>
</x-layouts.app>
