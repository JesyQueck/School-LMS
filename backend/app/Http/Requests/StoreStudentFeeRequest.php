<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'amount_expected' => ['required', 'numeric', 'gte:0'],
        ];
    }
}
