<?php

namespace App\Services;

use App\Models\ClassAssignment;
use App\Models\TeacherClassSubject;
use App\Models\User;

class TeacherDashboardService
{
    public function canAccessClassFeatures(User $user): bool
    {
        if ($user->role !== 'teacher') {
            return false;
        }

        $teacher = $user->teacher;
        if (! $teacher) {
            return false;
        }

        return ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->exists();
    }

    public function getClassAssignment(User $user): ?ClassAssignment
    {
        if ($user->role !== 'teacher' || ! $user->teacher) {
            return null;
        }

        return ClassAssignment::where('teacher_id', $user->teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->first();
    }

    public function getSubjectAssignments(User $user)
    {
        if ($user->role !== 'teacher' || ! $user->teacher) {
            return collect();
        }

        return TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
            ->where('teacher_id', $user->teacher->id)
            ->where('is_active', true)
            ->get();
    }

    public function getAllowedRoutes(User $user): array
    {
        if ($user->role !== 'teacher') {
            return [];
        }

        $routes = [
            'teacher.dashboard',
            'teacher.attendance.store',
        ];

        if ($this->canAccessClassFeatures($user)) {
            $routes = array_merge($routes, [
                'teacher.attendance.view',
                'teacher.report_cards.generate',
                'teacher.behaviour.assess',
                'teacher.parents.communicate',
                'teacher.class.performance',
            ]);
        }

        $routes = array_merge($routes, [
            'subjects.results.enter',
            'subjects.assessments.enter',
            'subjects.assignments.enter',
        ]);

        return array_unique($routes);
    }
}
