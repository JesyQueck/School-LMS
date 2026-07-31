<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the parent can view the student.
     *
     * A parent may only access students linked to them via the
     * parent_student pivot table.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->role !== 'parent') {
            return false;
        }

        $parent = $user->parentProfile;

        if (! $parent) {
            return false;
        }

        return $parent->students()
            ->where('students.id', $student->id)
            ->exists();
    }
}
