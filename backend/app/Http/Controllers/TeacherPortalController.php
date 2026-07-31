<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $teacher = Teacher::where('user_id', $request->user()->id)->first();

        $assignments = collect();
        if ($teacher) {
            $assignments = TeacherClassSubject::with(['classSubject.class', 'classSubject.subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get();
        }

        return view('dashboard.teacher', [
            'assignments' => $assignments,
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        Attendance::create(array_merge($data, [
            'marked_by' => $request->user()->id,
        ]));

        return redirect()->route('teacher.dashboard')->with('status', 'Attendance recorded.');
    }
}
