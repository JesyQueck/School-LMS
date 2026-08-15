<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $student = Student::where('user_id', $request->user()->id)->with('schoolClass')->first();

        $timetable = Timetable::with([
            'classSubject.subject',
            'classSubject.class',
            'teacher.user',
        ])
            ->whereHas('classSubject.class', function ($query) use ($student) {
                $query->where('id', $student->class_id);
            })
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $periods = $timetable->map(function (Timetable $entry, $index) {
            return [
                'period' => $index + 1,
                'subject' => $entry->classSubject->subject->name ?? 'Unknown Subject',
                'teacher' => $entry->teacher && $entry->teacher->user
                    ? $entry->teacher->user->name
                    : 'Not assigned',
                'day' => $entry->day,
                'start_time' => $entry->start_time,
                'end_time' => $entry->end_time,
            ];
        });

        return view('student.timetable', compact('student', 'periods'));
    }
}
