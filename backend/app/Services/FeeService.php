<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\StudentFee;
use App\Models\User;
use Illuminate\Database\QueryException;
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

        $receiptNumber = $data['receipt_number'] ?? null;
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () use ($studentFee, $receiptNumber, $amountPaid, $recordedBy, $data) {
                    $payment = Payment::create([
                        'student_fee_id' => $studentFee->id,
                        'receipt_number' => $receiptNumber ?? $this->generateReceiptNumber(),
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
            } catch (QueryException $e) {
                if ($attempt < $maxAttempts && $this->isDuplicateReceiptError($e)) {
                    $receiptNumber = $this->generateReceiptNumber();

                    continue;
                }

                throw $e;
            }
        }

        throw new \RuntimeException('Unable to generate a unique receipt number after maximum attempts.');
    }

    private function isDuplicateReceiptError(QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'receipt_number')
            && str_contains($e->getMessage(), 'Duplicate');
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

    /**
     * School-wide finance summary.
     *
     * @return array{expected:float, collected:float, outstanding:float, collection_rate:float, paid:int, partial:int, unpaid:int}
     */
    public function financeSummary(): array
    {
        $expected = (float) StudentFee::sum('amount_expected');
        $collected = (float) Payment::sum('amount_paid');

        $paid = StudentFee::whereRaw('amount_expected <= (SELECT COALESCE(SUM(amount_paid), 0) FROM payments WHERE payments.student_fee_id = student_fees.id)')->count();
        $unpaid = StudentFee::whereDoesntHave('payments')->count();
        $total = StudentFee::count();
        $partial = max(0, $total - $paid - $unpaid);

        return [
            'expected' => $expected,
            'collected' => $collected,
            'outstanding' => max(0, $expected - $collected),
            'collection_rate' => $expected > 0 ? round(($collected / $expected) * 100, 1) : 0,
            'paid' => $paid,
            'partial' => $partial,
            'unpaid' => $unpaid,
        ];
    }
}
