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

    public function validated(): array
    {
        $data = parent::validated();

        $data['payment_method'] = $data['payment_method'] ?? 'bank_transfer';
        $data['paid_at'] = $data['paid_at'] ?? now()->toDateTimeString();

        return $data;
    }
}
