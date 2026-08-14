<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'results' => ['required', 'array'],
            'results.*.ca_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'results.*.exam_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'results.*.remark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
