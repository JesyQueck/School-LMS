<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'import_file' => [
                'required',
                'file',
                'mimes:csv,xlsx',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'import_file.required' => 'Please select a file to import.',
            'import_file.file' => 'The selected file is not valid.',
            'import_file.mimes' => 'Only CSV and Excel (.xlsx) files are supported.',
            'import_file.max' => 'File size must not exceed 5MB.',
        ];
    }
}
