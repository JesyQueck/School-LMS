<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\StudentFee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeeService
{
    /**
     * Record a payment against a student fee obligation.
     *
     * Business rules:
     * - A payment may not exceed the outstanding balance.
     * - The fee status is recomputed as paid/partial/unpaid.
     * - The receipt number is generated automatically.
     * - The whole operation is wrapped in a transaction.
     */
    public function recordPayment(StudentFee $studentFee, array $data, User $recordedBy): Payment
    {
        $amountPaid = (float) $data['amount_paid'];
        $outstanding = $this->outstandingBalance($studentFee);

        if ($amountPaid > $outstanding) {
            throw new \InvalidArgumentException(
                'Payment amount exceeds the outstanding balance.'
            );
        }

        return DB::transaction(function () use ($studentFee, $data, $recordedBy, $amountPaid) {
            $payment = Payment::create([
                'student_fee_id' => $studentFee->id,
                'receipt_number' => $data['receipt_number'] ?? $this->generateReceiptNumber(),
                'amount_paid' => $amountPaid,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_date' => $data['payment_date'],
                'recorded_by' => $recordedBy->id,
            ]);

            $studentFee->update([
                'status' => $this->recomputeStatus($studentFee),
            ]);

            return $payment;
        });
    }

    /**
     * Recompute the status of a fee obligation based on total paid.
     */
    public function recomputeStatus(StudentFee $studentFee): string
    {
        $totalPaid = (float) $studentFee->payments()->sum('amount_paid');
        $expected = (float) $studentFee->amount_expected;

        if ($totalPaid <= 0) {
            return 'unpaid';
        }

        if ($totalPaid >= $expected) {
            return 'paid';
        }

        return 'partial';
    }

    /**
     * The remaining amount still owed on a fee obligation.
     */
    public function outstandingBalance(StudentFee $studentFee): float
    {
        $totalPaid = (float) $studentFee->payments()->sum('amount_paid');
        $expected = (float) $studentFee->amount_expected;

        return max(0, $expected - $totalPaid);
    }

    /**
     * Generate a unique receipt number.
     */
    public function generateReceiptNumber(): string
    {
        return 'RCPT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
