<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_fee_id' => ['required', 'exists:student_fees,id'],
            'receipt_number' => ['nullable', 'string', 'max:255'],
            'amount_paid' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
