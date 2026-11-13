<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordPayrollPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,cheque,mobile_money'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ];
    }

    public function validatedData(): array
    {
        $data = $this->validated();

        return [
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'payment_reference' => $data['payment_reference'] ?? null,
            'paid_at' => $data['paid_at'] ?? now()->toDateTimeString(),
        ];
    }
}
