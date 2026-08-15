<?php

namespace App\Policies;

use App\Models\ReportCard;
use App\Models\User;

class ReportCardPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher', 'parent']);
    }

    public function view(User $user, ReportCard $reportCard): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'parent') {
            return $user->parentProfile
                && $user->parentProfile->students()
                    ->where('students.id', $reportCard->student_id)
                    ->exists();
        }

        if ($user->role === 'teacher') {
            return $reportCard->isPublished();
        }

        return false;
    }

    public function publish(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function approve(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }
}
