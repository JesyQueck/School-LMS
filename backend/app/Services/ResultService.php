<?php

namespace App\Services;

use App\Models\Result;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

class ResultService
{
    public function calculateGrade(?float $total): array
    {
        if ($total === null) {
            return ['grade' => null, 'remark' => null];
        }

        return match (true) {
            $total >= 75 => ['grade' => 'A1', 'remark' => 'Excellent'],
            $total >= 70 => ['grade' => 'B2', 'remark' => 'Very Good'],
            $total >= 65 => ['grade' => 'B3', 'remark' => 'Good'],
            $total >= 60 => ['grade' => 'C4', 'remark' => 'Credit'],
            $total >= 55 => ['grade' => 'C5', 'remark' => 'Credit'],
            $total >= 50 => ['grade' => 'C6', 'remark' => 'Credit'],
            $total >= 45 => ['grade' => 'D7', 'remark' => 'Pass'],
            $total >= 40 => ['grade' => 'E8', 'remark' => 'Pass'],
            default => ['grade' => 'F9', 'remark' => 'Fail'],
        };
    }

    public function createResult(array $data, User $submittedBy): Result
    {
        $this->validateScores($data['ca_score'] ?? null, $data['exam_score'] ?? null);

        $total = $this->calculateTotal($data['ca_score'] ?? null, $data['exam_score'] ?? null);
        $grading = $this->calculateGrade($total);

        return Result::create([
            'student_id' => $data['student_id'],
            'class_subject_id' => $data['class_subject_id'],
            'term_id' => $data['term_id'],
            'ca_score' => $data['ca_score'] ?? null,
            'exam_score' => $data['exam_score'] ?? null,
            'total' => $total,
            'grade' => $grading['grade'],
            'remark' => $data['remark'] ?? $grading['remark'],
            'submitted_by' => $submittedBy->id,
            'is_locked' => false,
        ]);
    }

    public function updateOrCreateResult(array $data, User $submittedBy): Result
    {
        $existing = Result::where('student_id', $data['student_id'])
            ->where('class_subject_id', $data['class_subject_id'])
            ->where('term_id', $data['term_id'])
            ->first();

        if ($existing && $existing->isLocked()) {
            throw new RuntimeException('This result has been locked and cannot be modified.');
        }

        $this->validateScores($data['ca_score'] ?? null, $data['exam_score'] ?? null);

        $total = $this->calculateTotal($data['ca_score'] ?? null, $data['exam_score'] ?? null);
        $grading = $this->calculateGrade($total);

        if ($existing) {
            $existing->update([
                'ca_score' => $data['ca_score'] ?? $existing->ca_score,
                'exam_score' => $data['exam_score'] ?? $existing->exam_score,
                'total' => $total,
                'grade' => $grading['grade'],
                'remark' => $data['remark'] ?? $existing->remark,
                'submitted_by' => $submittedBy->id,
            ]);

            return $existing->fresh();
        }

        return $this->createResult($data, $submittedBy);
    }

    public function lockResult(Result $result): Result
    {
        $result->update(['is_locked' => true]);

        return $result->refresh();
    }

    public function calculateTotal(?float $caScore, ?float $examScore): ?float
    {
        if ($caScore === null && $examScore === null) {
            return null;
        }

        return ($caScore ?? 0) + ($examScore ?? 0);
    }

    protected function validateScores(?float $caScore, ?float $examScore): void
    {
        $errors = [];

        if ($caScore !== null) {
            if ($caScore < 0) {
                $errors[] = 'CA score cannot be negative.';
            }

            if ($caScore > 100) {
                $errors[] = 'CA score cannot exceed 100.';
            }
        }

        if ($examScore !== null) {
            if ($examScore < 0) {
                $errors[] = 'Exam score cannot be negative.';
            }

            if ($examScore > 100) {
                $errors[] = 'Exam score cannot exceed 100.';
            }
        }

        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }
    }
}
