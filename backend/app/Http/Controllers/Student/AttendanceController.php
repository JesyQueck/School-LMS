<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $attendance = $student->attendance()
            ->with(['term', 'class'])
            ->latest('date')
            ->get();

        return view('student.attendance', compact('student', 'attendance'));
    }

    public function timetable(Request $request)
    {
        $student = $request->user()->student;

        $student->load(['class.classSubjects.subject', 'class.classSubjects.timetable' => function ($q) {
            $q->with('subject', 'teacher');
        }]);

        return view('student.timetable', compact('student'));
    }
}
