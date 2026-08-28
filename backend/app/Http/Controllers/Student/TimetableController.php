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

        abort_if(! $student, 403, 'Student profile not found.');

        $timetable = Timetable::with([
            'classSubject.subject',
            'classSubject.class',
            'teacher.user',
            'periodConfig.periods',
        ])
            ->whereHas('classSubject.class', function ($query) use ($student) {
                $query->where('id', $student->class_id);
            })
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $periods = collect();
        if ($timetable->isNotEmpty()) {
            if ($timetable->first()->periodConfig) {
                $periods = $timetable->first()->periodConfig->periods->sortBy('sort_order');
            } else {
                $periods = $timetable->map(function ($entry) {
                    return (object) [
                        'name' => $entry->start_time->format('g:ia'),
                        'start_time' => $entry->start_time,
                        'end_time' => $entry->end_time,
                        'is_break' => false,
                        'sort_order' => 0,
                    ];
                })->sortBy('start_time')->values();
            }
        }

        return view('student.timetable', compact('student', 'periods', 'timetable'));
    }

}
