<?php

namespace App\Services;

use App\Models\FeeComponent;
use App\Models\FeePackageAssignment;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\StudentFeeComponentSnapshot;
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
                        'reference' => $data['reference'] ?? null,
                        'notes' => $data['notes'] ?? null,
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

    /**
     * Reverse a payment.
     *
     * Business rules:
     * - Payment must exist and not already be reversed.
     * - Reason is mandatory.
     * - Original payment is preserved.
     * - Balance and status are recalculated.
     * - Audit log entry is created.
     */
    public function reversePayment(Payment $payment, string $reason, User $reversedBy): PaymentReversal
{
    return DB::transaction(function () use ($payment, $reason, $reversedBy) {
        $lockedPayment = Payment::whereKey($payment->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($lockedPayment->isReversed()) {
            throw new \InvalidArgumentException(
                'This payment has already been reversed.'
            );
        }

        $reversal = PaymentReversal::create([
            'payment_id' => $lockedPayment->id,
            'reversed_by' => $reversedBy->id,
            'reason' => $reason,
            'amount_reversed' => $lockedPayment->amount_paid,
            'reversed_at' => now(),
        ]);

        $studentFee = StudentFee::whereKey($lockedPayment->student_fee_id)
            ->lockForUpdate()
            ->firstOrFail();

        $studentFee->update([
            'status' => $this->recomputeStatus($studentFee),
        ]);

        return $reversal;
    });
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
        $totalPaid = (float) $studentFee->payments()->whereDoesntHave('reversal')->sum('amount_paid');
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
     * Excludes reversed payments.
     */
    public function outstandingBalance(StudentFee $studentFee): float
    {
        $totalPaid = (float) $studentFee->payments()->whereDoesntHave('reversal')->sum('amount_paid');
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

    public function createStudentFeesForFeeType(FeeType $feeType): void
    {
        if ($feeType->class_id) {
            $students = Student::where('class_id', $feeType->class_id)->get();
        } else {
            $students = Student::all();
        }

        DB::transaction(function () use ($feeType, $students) {
            foreach ($students as $student) {
                StudentFee::firstOrCreate(
                    ['student_id' => $student->id, 'fee_type_id' => $feeType->id, 'term_id' => $feeType->term_id],
                    [
                        'amount_expected' => $feeType->amount,
                        'status' => 'unpaid',
                    ]
                );
            }
        });
    }

    /**
     * Create student fee obligations from a fee package assignment.
     * Creates one StudentFee per student with the package total,
     * and creates component snapshots for historical preservation.
     */
    public function createStudentFeesForPackageAssignment(FeePackageAssignment $assignment): void
    {
        $package = $assignment->package()->with('components')->firstOrFail();

        if (! $package->is_active || ! $assignment->is_active) {
            return;
        }

        $students = Student::where('class_id', $assignment->class_id)->get();
        $components = $package->components;
        $totalAmount = $components->sum('amount');

        DB::transaction(function () use ($students, $assignment, $package, $components, $totalAmount) {
            foreach ($students as $student) {
                $studentFee = StudentFee::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'fee_type_id' => $package->id,
                        'fee_source_type' => 'fee_package',
                        'fee_package_id' => $package->id,
                        'term_id' => $assignment->term_id,
                    ],
                    [
                        'amount_expected' => $totalAmount,
                        'status' => 'unpaid',
                    ]
                );

                // Create component snapshots for historical preservation
                foreach ($components as $component) {
                    StudentFeeComponentSnapshot::firstOrCreate(
                        [
                            'student_fee_id' => $studentFee->id,
                            'fee_component_id' => $component->id,
                        ],
                        [
                            'component_name' => $component->name,
                            'component_amount' => $component->amount,
                            'component_description' => $component->description,
                        ]
                    );
                }
            }
        });
    }

    /**
     * School-wide finance summary.
     * Excludes reversed payments from collected amount.
     *
     * @return array{expected:float, collected:float, outstanding:float, collection_rate:float, paid:int, partial:int, unpaid:int}
     */
    public function financeSummary(): array
    {
        $expected = (float) StudentFee::sum('amount_expected');
        $collected = (float) Payment::whereDoesntHave('reversal')->sum('amount_paid');

        // Count paid/partial/unpaid using the same status logic as recomputeStatus(),
        // which excludes reversed payments from the outstanding balance calculation.
        $paid = 0;
        $unpaid = 0;
        $partial = 0;

        StudentFee::chunk(500, function ($fees) use (&$paid, &$partial, &$unpaid) {
            foreach ($fees as $fee) {
                $status = $this->recomputeStatus($fee);
                match ($status) {
                    'paid' => $paid++,
                    'partial' => $partial++,
                    default => $unpaid++,
                };
            }
        });

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

    /**
     * Per-class finance breakdown.
     * Excludes reversed payments.
     *
     * @return array<int, array{class:string, expected:float, collected:float, outstanding:float, collection_rate:float, paid:int, partial:int, unpaid:int, total:int}>
     */
    public function classSummary(): array
    {
        return SchoolClass::withCount('students')->get()->map(function (SchoolClass $class) {
            $studentIds = Student::where('class_id', $class->id)->pluck('id');

            $fees = StudentFee::whereIn('student_id', $studentIds);

            $expected = (float) $fees->sum('amount_expected');
            $collected = (float) Payment::whereIn('student_fee_id', $fees->pluck('id'))
                ->whereDoesntHave('reversal')
                ->sum('amount_paid');

            $paidCount = 0;
            $partialCount = 0;
            $unpaidCount = 0;

            foreach ($fees->get() as $fee) {
                $status = $this->recomputeStatus($fee);
                match ($status) {
                    'paid' => $paidCount++,
                    'partial' => $partialCount++,
                    default => $unpaidCount++,
                };
            }

            return [
                'class' => $class->name,
                'expected' => $expected,
                'collected' => $collected,
                'outstanding' => max(0, $expected - $collected),
                'collection_rate' => $expected > 0 ? round(($collected / $expected) * 100, 1) : 0,
                'paid' => $paidCount,
                'partial' => $partialCount,
                'unpaid' => $unpaidCount,
                'total' => $class->students_count,
            ];
        })->values()->toArray();
    }
}