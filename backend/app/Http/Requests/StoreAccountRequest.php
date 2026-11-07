<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAccountRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('email')) {
            $email = $this->input('email');
            $normalized['email'] = filled($email)
                ? strtolower(trim((string) $email))
                : null;
        }

        if ($this->has('staff_id')) {
            $normalized['staff_id'] = strtoupper(trim((string) $this->input('staff_id')));
        }

        if ($this->has('admission_no')) {
            $normalized['admission_no'] = strtoupper(trim((string) $this->input('admission_no')));
        }

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:teacher,student,parent,bursar'],
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                Rule::requiredIf(fn () => $this->input('type') !== 'student'),
                'nullable',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $identifier = mb_strtolower(trim((string) $value));

                    if (
                        User::whereRaw('LOWER(email) = ?', [$identifier])->exists()
                        || User::whereRaw('LOWER(staff_id) = ?', [$identifier])->exists()
                        || Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])->exists()
                        || Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists()
                    ) {
                        $fail('This email conflicts with another login ID.');
                    }
                },
            ],

            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [
                'nullable',
                'string',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],

            'staff_id' => [
                Rule::requiredIf(fn () => in_array($this->input('type'), ['teacher', 'bursar'], true)),
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $identifier = mb_strtolower(trim((string) $value));

                    if (
                        User::whereRaw('LOWER(staff_id) = ?', [$identifier])->exists()
                        || Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])->exists()
                        || User::whereRaw('LOWER(email) = ?', [$identifier])->exists()
                        || Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists()
                    ) {
                        $fail('This Staff ID is already used by another login identifier.');
                    }
                },
            ],

            'admission_no' => [
                Rule::requiredIf(fn () => $this->input('type') === 'student'),
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $identifier = mb_strtolower(trim((string) $value));

                    if (
                        Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists()
                        || User::whereRaw('LOWER(staff_id) = ?', [$identifier])->exists()
                        || Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])->exists()
                        || User::whereRaw('LOWER(email) = ?', [$identifier])->exists()
                    ) {
                        $fail('This Admission Number is already used by another login identifier.');
                    }
                },
            ],
        ];
    }
}
