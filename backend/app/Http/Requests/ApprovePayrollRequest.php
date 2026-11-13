<?php

namespace App\Http\Requests;

use App\Models\PayrollRecord;
use Illuminate\Foundation\Http\FormRequest;

class ApprovePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record' => ['sometimes', 'string'],
        ];
    }

    public function getValidatedRecord(): ?PayrollRecord
    {
        if (! $this->route('payrollRecord')) {
            return null;
        }

        return $this->route('payrollRecord');
    }
}
