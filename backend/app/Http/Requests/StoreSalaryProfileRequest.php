<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => filter_var(
                $this->input('is_active', true),
                FILTER_VALIDATE_BOOLEAN
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'basic_salary' => ['required', 'numeric', 'gte:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['required', 'boolean'],

            'components' => ['sometimes', 'array'],
            'components.*' => ['array'],
            'components.*.enabled' => ['nullable', 'boolean'],
            'components.*.component_id' => [
                'required',
                'integer',
                'exists:salary_components,id',
            ],
            'components.*.value' => ['nullable', 'numeric', 'gte:0'],
        ];
    }
}