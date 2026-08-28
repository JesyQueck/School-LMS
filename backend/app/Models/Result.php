<?php

namespace App\Models;

use App\Services\ResultGradeCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class Result extends Model
{
    protected $fillable = [
        'student_id',
        'class_subject_id',
        'term_id',
        'ca_score',
        'exam_score',
        'total',
        'grade',
        'remark',
        'submitted_by',
        'is_locked',
    ];

    protected $casts = [
        'ca_score' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Result $result) {
            $classSubject = ClassSubject::find($result->class_subject_id);
            $caMax = $classSubject->ca_max ?? 30;
            $examMax = $classSubject->exam_max ?? 70;

            if (filled($result->ca_score) && $result->ca_score < 0) {
                throw new RuntimeException('CA score cannot be negative.');
            }

            if (filled($result->ca_score) && $result->ca_score > $caMax) {
                throw new RuntimeException("CA score cannot exceed {$caMax}.");
            }

            if (filled($result->exam_score) && $result->exam_score < 0) {
                throw new RuntimeException('Exam score cannot be negative.');
            }

            if (filled($result->exam_score) && $result->exam_score > $examMax) {
                throw new RuntimeException("Exam score cannot exceed {$examMax}.");
            }

            if (filled($result->ca_score) && $result->ca_score > 100) {
                throw new RuntimeException('CA score cannot exceed 100.');
            }

            if (filled($result->exam_score) && $result->exam_score > 100) {
                throw new RuntimeException('Exam score cannot exceed 100.');
            }

            if ($result->ca_score !== null || $result->exam_score !== null) {
                $result->total = ($result->ca_score ?? 0) + ($result->exam_score ?? 0);

                $scoresChanged = $result->isDirty('ca_score') || $result->isDirty('exam_score');
                $grading = ResultGradeCalculator::calculateGrade($result->total);

                if ($scoresChanged && ! $result->isDirty('grade')) {
                    $result->grade = $grading['grade'];
                }

                if ($scoresChanged && ! $result->isDirty('remark')) {
                    $result->remark = $grading['remark'];
                }
            }
        });

        static::updating(function (Result $result) {
            if ($result->is_locked && ! $result->isDirty('is_locked')) {
                throw new RuntimeException('This result has been locked and cannot be modified.');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    public function canEdit(): bool
    {
        return ! $this->isLocked();
    }
}
