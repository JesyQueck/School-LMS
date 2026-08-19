<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentContact extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'full_name',
        'phone',
        'whatsapp',
        'email',
        'occupation',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
