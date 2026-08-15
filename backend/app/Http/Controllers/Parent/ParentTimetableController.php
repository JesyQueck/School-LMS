<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentTimetableController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user()->parentProfile;
        $children = $parent
            ? $parent->students()->with(['schoolClass', 'user'])->get()
            : collect();

        $selectedId = $request->query('student');
        $selected = $children->firstWhere('id', $selectedId) ?? $children->first();

        $periods = $selected ? $this->buildPeriods($selected) : collect();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return view('parent.timetable', compact('children', 'selected', 'periods', 'days'));
    }

    protected function buildPeriods(Student $student)
    {
        $classId = $student->class_id;
        if (! $classId) {
            return collect();
        }

        $classSubjects = ClassSubject::with(['subject', 'teacherAssignments.teacher.user'])
            ->where('class_id', $classId)
            ->get();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $periods = collect();
        $dayIndex = 0;
        $periodNumber = 1;

        foreach ($classSubjects as $index => $cs) {
            $teacher = $cs->teacherAssignments->first()?->teacher?->user;
            $periods->push([
                'day' => $days[$dayIndex % count($days)],
                'period' => $periodNumber,
                'subject' => $cs->subject->name ?? 'Unknown Subject',
                'teacher' => $teacher ? $teacher->name : 'Not assigned',
            ]);

            $dayIndex++;
            if ($dayIndex % count($days) === 0) {
                $periodNumber++;
            }
        }

        return $periods->sortBy(['day', 'period'])->values();
    }
}
