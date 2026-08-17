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

    public function classes(): HasMany
    {
        return $this->hasManyThrough(SchoolClass::class, ClassSubject::class, 'subject_id', 'id', 'id', 'class_id');
    }

    public function updateClassSubjects(array $classIds): void
    {
        $currentClassIds = $this->classSubjects()->pluck('class_id')->toArray();

        $toAttach = array_diff($classIds, $currentClassIds);
        $toDetach = array_diff($currentClassIds, $classIds);

        foreach ($toAttach as $classId) {
            ClassSubject::firstOrCreate([
                'class_id' => $classId,
                'subject_id' => $this->id,
            ]);
        }

        if (! empty($toDetach)) {
            ClassSubject::where('subject_id', $this->id)
                ->whereIn('class_id', $toDetach)
                ->delete();
        }
    }
}
