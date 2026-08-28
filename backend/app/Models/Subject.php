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

    public function updateClassSubjects(array $classIds, array $scoreSettings = []): void
    {
        $currentClassIds = $this->classSubjects()->pluck('class_id')->toArray();

        $toAttach = array_diff($classIds, $currentClassIds);
        $toDetach = array_diff($currentClassIds, $classIds);

        $settingsByClassId = [];
        foreach ($scoreSettings as $setting) {
            $settingsByClassId[$setting['class_id']] = $setting;
        }

        foreach ($toAttach as $classId) {
            $settings = $settingsByClassId[$classId] ?? [];
            ClassSubject::firstOrCreate(
                ['class_id' => $classId, 'subject_id' => $this->id],
                [
                    'periods_per_week' => 1,
                    'ca_max' => $settings['ca_max'] ?? 30,
                    'exam_max' => $settings['exam_max'] ?? 70,
                ]
            );
        }

        $this->classSubjects()
            ->whereIn('class_id', $classIds)
            ->whereNotIn('class_id', $toAttach)
            ->each(function ($classSubject) use ($settingsByClassId) {
                $settings = $settingsByClassId[$classSubject->class_id] ?? [];
                if (isset($settings['ca_max'])) {
                    $classSubject->update(['ca_max' => $settings['ca_max']]);
                }
                if (isset($settings['exam_max'])) {
                    $classSubject->update(['exam_max' => $settings['exam_max']]);
                }
            });

        if (! empty($toDetach)) {
            ClassSubject::where('subject_id', $this->id)
                ->whereIn('class_id', $toDetach)
                ->delete();
        }
    }
}
