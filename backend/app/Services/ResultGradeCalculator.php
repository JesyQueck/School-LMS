<?php

namespace App\Services;

use InvalidArgumentException;

class ResultGradeCalculator
{
    public const GRADE_BOUNDARIES = [
        75 => ['grade' => 'A1', 'remark' => 'Excellent'],
        70 => ['grade' => 'B2', 'remark' => 'Very Good'],
        65 => ['grade' => 'B3', 'remark' => 'Good'],
        60 => ['grade' => 'C4', 'remark' => 'Credit'],
        55 => ['grade' => 'C5', 'remark' => 'Credit'],
        50 => ['grade' => 'C6', 'remark' => 'Credit'],
        45 => ['grade' => 'D7', 'remark' => 'Pass'],
        40 => ['grade' => 'E8', 'remark' => 'Pass'],
    ];

    public const FAILING_GRADE = ['grade' => 'F9', 'remark' => 'Fail'];

    public static function calculateTotal(?float $caScore, ?float $examScore): ?float
    {
        if ($caScore === null && $examScore === null) {
            return null;
        }

        return ($caScore ?? 0) + ($examScore ?? 0);
    }

    public static function calculateGrade(?float $total): array
    {
        if ($total === null) {
            return ['grade' => null, 'remark' => null];
        }

        foreach (self::GRADE_BOUNDARIES as $minScore => $grading) {
            if ($total >= $minScore) {
                return $grading;
            }
        }

        return self::FAILING_GRADE;
    }

    public static function validateScores(?float $caScore, ?float $examScore): void
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
