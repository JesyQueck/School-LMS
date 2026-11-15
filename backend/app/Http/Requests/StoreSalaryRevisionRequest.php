<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'basic_salary' => ['required', 'numeric', 'gt:0'],
            'effective_from' => ['required', 'date_format:Y-m-d'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'components' => ['sometimes', 'array'],
            'components.*' => ['array'],
            'components.*.enabled' => ['nullable', 'boolean'],
            'components.*.component_id' => [
                'required', 'integer', 'distinct', 'exists:salary_components,id',
            ],
            'components.*.value' => ['nullable', 'numeric', 'gte:0'],
        ];
    }
}