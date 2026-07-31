<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Show a child's attendance history for the current term.
     */
    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['class']);

        $attendance = $student->attendance()
            ->with(['term', 'class'])
            ->latest('date')
            ->get();

        return view('parent.attendance', compact('student', 'attendance'));
    }
}
