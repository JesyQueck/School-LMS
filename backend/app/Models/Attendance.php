<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'student_id',
        'class_id',
        'term_id',
        'date',
        'status',
        'is_synthetic',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_synthetic' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function scopeReal(Builder $query): Builder
    {
        return Schema::hasColumn($this->getTable(), 'is_synthetic')
            ? $query->where('is_synthetic', false)
            : $query;
    }
}
