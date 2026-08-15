<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fee_types')->where(function ($query) {
                    $query->where('term_id', $this->input('term_id'));
                    if ($this->filled('class_id')) {
                        $query->where('class_id', $this->input('class_id'));
                    } else {
                        $query->whereNull('class_id');
                    }
                }),
            ],
            'amount' => ['required', 'numeric'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ];
    }
}
