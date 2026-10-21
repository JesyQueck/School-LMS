<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAccountRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $email = $this->input('email');
            $this->merge(['email' => filled($email) ? strtolower(trim((string) $email)) : null]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:teacher,student,parent'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                Rule::requiredIf(fn () => $this->input('type') !== 'student'),
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $identifier = mb_strtolower(trim((string) $value));
                    if (User::whereRaw('LOWER(email) = ?', [$identifier])->exists()
                        || ($this->input('type') !== 'student' && (
                            Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists()
                            || Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])->exists()
                        ))) {
                        $fail('This email conflicts with another login ID.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }
}
