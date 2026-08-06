<?php

namespace App\Services;

use App\Models\Result;
use App\Models\User;

class ResultService
{
    /**
     * The Nigerian secondary-school grading scale (PRD §6).
     *
     * @return array{0: string|null, 1: string|null, grade: string|null, remark: string|null}
     */
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

    /**
     * Create a result, computing total/grade/remark and enforcing the
     * term-lock business rule (results cannot be added to a locked term).
     *
     * @param  array  $data  student_id, class_subject_id, term_id, ca_score, exam_score, remark
     */
    public function createResult(array $data, User $submittedBy): Result
    {
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

    /**
     * Lock a result so it can no longer be edited by teachers.
     */
    public function lockResult(Result $result): Result
    {
        $result->update(['is_locked' => true]);

        return $result->refresh();
    }

    /**
     * Compute the total score from CA and exam scores.
     */
    public function calculateTotal(?float $caScore, ?float $examScore): ?float
    {
        if ($caScore === null && $examScore === null) {
            return null;
        }

        return ($caScore ?? 0) + ($examScore ?? 0);
    }
}
