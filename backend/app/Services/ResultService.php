<?php

namespace App\Services;

use App\Models\Result;
use App\Models\User;
use RuntimeException;

class ResultService
{
    public function calculateGrade(?float $total): array
    {
        return ResultGradeCalculator::calculateGrade($total);
    }

    public function calculateTotal(?float $caScore, ?float $examScore): ?float
    {
        return ResultGradeCalculator::calculateTotal($caScore, $examScore);
    }

    public function createResult(array $data, User $submittedBy): Result
    {
        ResultGradeCalculator::validateScores($data['ca_score'] ?? null, $data['exam_score'] ?? null);

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

        ResultGradeCalculator::validateScores($data['ca_score'] ?? null, $data['exam_score'] ?? null);

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
}
