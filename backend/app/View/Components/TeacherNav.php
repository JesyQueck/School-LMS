<?php

namespace App\View\Components;

use App\Models\ClassAssignment;
use App\Models\TeacherClassSubject;
use Illuminate\View\Component;

class TeacherNav extends Component
{
    public bool $isClassTeacher;

    public bool $isSubjectTeacher;

    public $subjectAssignments;

    public function __construct()
    {
        $user = auth()->user();

        $this->isClassTeacher = false;
        $this->isSubjectTeacher = false;
        $this->subjectAssignments = collect();

        if ($user && $user->role === 'teacher' && $user->teacher) {
            $teacher = $user->teacher;

            $this->isClassTeacher = ClassAssignment::where('teacher_id', $teacher->id)
                ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
                ->exists();

            $this->isSubjectTeacher = TeacherClassSubject::where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->exists();

            $this->subjectAssignments = TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get();
        }
    }

    public function hasClassAssignment(): bool
    {
        return $this->isClassTeacher;
    }

    public function hasSubjectAssignments(): bool
    {
        return $this->isSubjectTeacher;
    }

    public function bothRoles(): bool
    {
        return $this->isClassTeacher && $this->isSubjectTeacher;
    }

    public function render()
    {
        return view('components.teacher-nav-menu');
    }
}
