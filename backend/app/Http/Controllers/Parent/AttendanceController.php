<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Show a child's attendance for the current term, or all available history if none is configured.
     */
    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['schoolClass']);

        $currentTerm = Term::with('academicSession')
            ->where('is_current', true)
            ->first();

        $attendanceQuery = $student->attendance()
            ->with(['term', 'class'])
            ->latest('date');

        if ($currentTerm) {
            $attendanceQuery->where('term_id', $currentTerm->id);
        }

        $attendance = $attendanceQuery->get();
        $hasAnyAttendance = $student->attendance()->exists();

        return view('parent.attendance', compact('student', 'attendance', 'currentTerm', 'hasAnyAttendance'));
    }
}
