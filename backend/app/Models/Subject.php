<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
    ];

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }
}
