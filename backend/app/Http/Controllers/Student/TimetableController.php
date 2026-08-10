<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        $periods = collect([
            [
                'period' => 1,
                'subject' => 'Mathematics',
                'teacher' => 'Mr. Johnson',
                'day' => 'Monday',
            ],
            [
                'period' => 2,
                'subject' => 'English',
                'teacher' => 'Ms. Smith',
                'day' => 'Monday',
            ],
            [
                'period' => 3,
                'subject' => 'Physics',
                'teacher' => 'Dr. Brown',
                'day' => 'Monday',
            ],
            [
                'period' => 1,
                'subject' => 'Chemistry',
                'teacher' => 'Dr. Lee',
                'day' => 'Tuesday',
            ],
            [
                'period' => 2,
                'subject' => 'Biology',
                'teacher' => 'Dr. Adams',
                'day' => 'Tuesday',
            ],
            [
                'period' => 3,
                'subject' => 'Mathematics',
                'teacher' => 'Mr. Johnson',
                'day' => 'Tuesday',
            ],
        ]);

        return view('student.timetable', compact('student', 'periods'));
    }
}
