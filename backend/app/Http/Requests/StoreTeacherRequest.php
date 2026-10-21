<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class StoreTeacherRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $identifier = mb_strtolower(trim((string) $value));
                    if (User::whereRaw('LOWER(email) = ?', [$identifier])->exists()
                        || Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists()
                        || Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])->exists()) {
                        $fail('This email conflicts with another login ID.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }
}
