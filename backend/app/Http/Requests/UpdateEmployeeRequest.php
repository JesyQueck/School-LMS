<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'employee_number' => ['required', 'string', 'max:50', 'unique:employees,employee_number,'.$employee->id],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->whereIn('role', ['admin', 'teacher', 'bursar'])
                ),
                'unique:employees,user_id,'.$employee->id,
            ],
            'employment_type' => ['required', 'in:full-time,part-time,contract'],
            'date_employed' => ['required', 'date'],
            'date_left' => ['nullable', 'date', 'after_or_equal:date_employed'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['required', 'in:active,on_leave,terminated,retired'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'pension_id' => ['nullable', 'string', 'max:50'],
        ];
    }
}