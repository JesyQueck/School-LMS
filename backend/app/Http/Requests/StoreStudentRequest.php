<?php

namespace App\Http\Requests;

use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value && User::whereRaw('LOWER(email) = ?', [mb_strtolower(trim((string) $value))])->exists()) {
                        $fail('This email address is already in use.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'class_id' => ['required', 'exists:classes,id'],
            'admission_no' => [
                'required',
                'string',
                'max:255',
                'unique:students,admission_no',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $identifier = mb_strtolower(trim((string) $value));
                    $conflictsWithAccount = User::whereIn('role', ['admin', 'teacher', 'parent'])
                        ->whereRaw('LOWER(email) = ?', [$identifier])
                        ->exists();
                    $conflictsWithStaffId = Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])->exists();
                    $duplicateAdmissionNo = Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists();

                    if ($conflictsWithAccount || $conflictsWithStaffId || $duplicateAdmissionNo
                        || preg_match('/^parent-\d+$/i', (string) $value)) {
                        $fail('This admission number conflicts with another login ID.');
                    }
                },
            ],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'state_of_origin' => ['nullable', 'string', 'max:100'],
            'lga' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'home_address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'document_passport' => ['nullable', 'image', 'max:2048'],

            'admission_date' => ['required', 'date'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'student_type' => ['required', 'in:new,returning,transfer'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'previous_school_address' => ['nullable', 'string', 'max:500'],
            'previous_class' => ['nullable', 'string', 'max:100'],
            'previous_year_attended' => ['nullable', 'string', 'max:10'],

            'parent_email' => ['required', 'email'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:20'],
            'parent_whatsapp' => ['nullable', 'string', 'max:20'],
            'parent_address' => ['nullable', 'string', 'max:500'],
            'parent_city' => ['nullable', 'string', 'max:100'],
            'parent_state' => ['nullable', 'string', 'max:100'],
            'parent_relationship' => ['nullable', 'string', 'max:100'],
            'parent_occupation' => ['nullable', 'string', 'max:255'],

            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'father_whatsapp' => ['nullable', 'string', 'max:20'],
            'father_email' => ['nullable', 'email', 'max:255'],
            'father_occupation' => ['nullable', 'string', 'max:255'],

            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'mother_whatsapp' => ['nullable', 'string', 'max:20'],
            'mother_email' => ['nullable', 'email', 'max:255'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],

            'emergency_1_name' => ['required', 'string', 'max:255'],
            'emergency_1_relationship' => ['required', 'string', 'max:100'],
            'emergency_1_phone' => ['required', 'string', 'max:20'],
            'emergency_1_whatsapp' => ['nullable', 'string', 'max:20'],
            'emergency_1_address' => ['nullable', 'string', 'max:500'],
            'emergency_2_name' => ['nullable', 'string', 'max:255'],
            'emergency_2_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_2_phone' => ['nullable', 'string', 'max:20'],
            'emergency_2_address' => ['nullable', 'string', 'max:500'],

            'document_birth_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_previous_result' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_transfer_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_identification' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_other' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],

            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('admission_no')) {
            $data['admission_no'] = trim((string) $this->input('admission_no'));
        }
        if ($this->has('email')) {
            $email = $this->input('email');
            $data['email'] = filled($email) ? strtolower(trim((string) $email)) : null;
        }
        if ($this->has('parent_email')) {
            $parentEmail = $this->input('parent_email');
            $data['parent_email'] = filled($parentEmail) ? strtolower(trim((string) $parentEmail)) : null;
        }

        $name = $this->input('name');

        if (! $this->filled('first_name') && $name) {
            $data['first_name'] = explode(' ', trim($name))[0];
        }
        if (! $this->filled('last_name') && $name) {
            $parts = explode(' ', trim($name));
            $data['last_name'] = count($parts) > 1 ? end($parts) : $parts[0];
        }

        if (! $this->filled('name') && $this->filled('first_name') && $this->filled('last_name')) {
            $data['name'] = trim($this->input('first_name').' '.$this->input('last_name'));
        }

        if (! $this->filled('parent_name') && $this->filled('parent_email')) {
            $data['parent_name'] = str_replace(['.', '_', '-'], ' ', explode('@', $this->input('parent_email'))[0]);
        }

        if (! $this->filled('admission_date')) {
            $data['admission_date'] = now()->toDateString();
        }
        if (! $this->filled('student_type')) {
            $data['student_type'] = 'new';
        }
        if (! $this->filled('academic_session_id')) {
            $currentSession = AcademicSession::where('is_current', true)->first();
            if ($currentSession) {
                $data['academic_session_id'] = $currentSession->id;
            }
        }

        $this->merge($data);
    }
}
