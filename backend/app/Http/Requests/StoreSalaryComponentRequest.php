<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:salary_components'],
            'description' => ['nullable', 'string'],
            'component_type' => ['required', 'in:allowance,deduction'],
            'calculation_type' => ['required', 'in:fixed,percentage'],
            'default_value' => ['required', 'numeric', 'gte:0'],
            'is_taxable' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function validated(): array
    {
        $data = parent::validated();

        $data['is_taxable'] = filter_var($data['is_taxable'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

        return $data;
    }
}
