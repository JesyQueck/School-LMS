<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $email = $this->input('email');
            $this->merge([
                'email' => filled($email) ? strtolower(trim((string) $email)) : null,
            ]);
        }

        if ($this->has('staff_id')) {
            $this->merge([
                'staff_id' => strtoupper(trim((string) $this->input('staff_id'))),
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.$teacher->user_id,
                function (string $attribute, mixed $value, \Closure $fail) use ($teacher): void {
                    $identifier = mb_strtolower(trim((string) $value));

                    if (
                        User::whereRaw('LOWER(email) = ?', [$identifier])
                            ->where('id', '!=', $teacher->user_id)->exists()
                        || User::whereRaw('LOWER(staff_id) = ?', [$identifier])
                            ->where('id', '!=', $teacher->user_id)->exists()
                        || Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists()
                        || Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])
                            ->where('id', '!=', $teacher->id)->exists()
                    ) {
                        $fail('This email conflicts with another login ID.');
                    }
                },
            ],

            'staff_id' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($teacher): void {
                    $identifier = mb_strtolower(trim((string) $value));

                    if (
                        User::whereRaw('LOWER(staff_id) = ?', [$identifier])
                            ->where('id', '!=', $teacher->user_id)->exists()
                        || Teacher::whereRaw('LOWER(employee_id) = ?', [$identifier])
                            ->where('id', '!=', $teacher->id)->exists()
                        || User::whereRaw('LOWER(email) = ?', [$identifier])
                            ->where('id', '!=', $teacher->user_id)->exists()
                        || Student::whereRaw('LOWER(admission_no) = ?', [$identifier])->exists()
                    ) {
                        $fail('This Staff ID is already being used by another account.');
                    }
                },
            ],

            'phone' => ['nullable', 'string', 'max:20'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }
}