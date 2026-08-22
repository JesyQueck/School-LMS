<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    protected $table = 'timetables';

    protected $fillable = [
        'class_subject_id',
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'term_id',
        'academic_session_id',
        'is_locked',
        'is_manual',
        'period_config_id',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_manual' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function periodConfig(): BelongsTo
    {
        return $this->belongsTo(PeriodConfig::class);
    }
}
