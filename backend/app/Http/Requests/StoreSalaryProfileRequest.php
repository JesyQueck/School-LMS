<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'basic_salary' => ['required', 'numeric', 'gte:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

        return $data;
    }
}
