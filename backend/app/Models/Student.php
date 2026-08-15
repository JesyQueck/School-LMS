<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'admission_no',
        'class_id',
        'first_name',
        'last_name',
        'house',
        'gender',
        'state_of_origin',
        'date_of_birth',
        'blood_group',
        'emergency_contact',
        'emergency_phone',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: ($this->user->name ?? $this->admission_no);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendances(): HasMany
    {
        return $this->attendance();
    }

    public function fees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, StudentFee::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentProfile::class, 'parent_student', 'student_id', 'parent_id');
    }
}
