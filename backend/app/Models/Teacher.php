<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'qualification',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classSubjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherClassSubject::class);
    }

    public function subjectAssignments()
    {
        return $this->hasMany(TeacherClassSubject::class)
            ->where('is_active', true);
    }

    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassAssignment::class);
    }

    public function currentClassAssignment(): BelongsTo
    {
        return $this->belongsTo(ClassAssignment::class, 'id')
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true));
    }

    public function hasClassAssignmentForSession($sessionId): bool
    {
        return $this->classAssignments()
            ->where('academic_session_id', $sessionId)
            ->exists();
    }
}
