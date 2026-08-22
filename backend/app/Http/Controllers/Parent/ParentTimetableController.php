<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Timetable;
use Carbon\Carbon;
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

        $periods = Timetable::with([
            'classSubject.subject',
            'teacher.user',
        ])
            ->whereHas('classSubject', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->orderByRaw(
                "CASE day WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 ELSE 6 END"
            )
            ->orderBy('start_time')
            ->get()
            ->map(function (Timetable $entry, $index) {
                return [
                    'period' => $index + 1,
                    'day' => $entry->day,
                    'subject' => $entry->classSubject->subject->name ?? 'Unknown Subject',
                    'teacher' => $entry->teacher && $entry->teacher->user
                        ? $entry->teacher->user->name
                        : 'Not assigned',
                    'start_time' => Carbon::parse($entry->start_time)->format('g:i A'),
                    'end_time' => Carbon::parse($entry->end_time)->format('g:i A'),
                ];
            })
            ->groupBy('day');

        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        foreach ($allDays as $day) {
            if (! $periods->has($day)) {
                $periods->put($day, collect());
            }
        }

        return $periods;
    }
}
