<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ];
    }
}
