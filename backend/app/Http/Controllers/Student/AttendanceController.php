<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        $student->load('schoolClass');

        $attendance = $student->attendance()
            ->with(['term', 'class'])
            ->latest('date')
            ->get();

        return view('student.attendance', compact('student', 'attendance'));
    }
}
