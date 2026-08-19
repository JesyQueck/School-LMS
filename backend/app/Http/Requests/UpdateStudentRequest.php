<?php

namespace App\Http\Requests;

use App\Models\AcademicSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$student->user_id],
            'phone' => ['nullable', 'string', 'max:20'],
            'class_id' => ['required', 'exists:classes,id'],
            'admission_no' => ['required', 'string', 'max:255', Rule::unique('students', 'admission_no')->ignore($student->id)],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'state_of_origin' => ['nullable', 'string', 'max:100'],
            'lga' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'home_address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],

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

            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

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
