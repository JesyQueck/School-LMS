<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSubject extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'is_compulsory',
        'periods_per_week',
        'ca_max',
        'exam_max',
    ];

    protected $casts = [
        'is_compulsory' => 'boolean',
        'ca_max' => 'integer',
        'exam_max' => 'integer',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherClassSubject::class, 'class_subject_id');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class, 'class_subject_id');
    }
}
