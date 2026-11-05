<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:fee_packages,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.name' => ['required', 'string', 'max:255'],
            'components.*.description' => ['nullable', 'string'],
            'components.*.amount' => ['required', 'numeric', 'gt:0'],
        ];
    }
}