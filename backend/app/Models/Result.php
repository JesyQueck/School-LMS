<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class Result extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ca_score' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Result $result) {
            if (filled($result->ca_score) && $result->ca_score < 0) {
                throw new RuntimeException('CA score cannot be negative.');
            }

            if (filled($result->ca_score) && $result->ca_score > 100) {
                throw new RuntimeException('CA score cannot exceed 100.');
            }

            if (filled($result->exam_score) && $result->exam_score < 0) {
                throw new RuntimeException('Exam score cannot be negative.');
            }

            if (filled($result->exam_score) && $result->exam_score > 100) {
                throw new RuntimeException('Exam score cannot exceed 100.');
            }

            if ($result->ca_score !== null || $result->exam_score !== null) {
                $result->total = ($result->ca_score ?? 0) + ($result->exam_score ?? 0);
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
