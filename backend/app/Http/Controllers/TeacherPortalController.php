<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Services\TeacherDashboardService;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    public function __construct(
        protected TeacherDashboardService $dashboardService,
    ) {}

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        $assignments = collect();
        if ($teacher) {
            $assignments = TeacherClassSubject::with(['classSubject.class', 'classSubject.subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get();
        }

        $classAssignment = $this->dashboardService->getClassAssignment($user);

        return view('dashboard.teacher', [
            'assignments' => $assignments,
            'classAssignment' => $classAssignment,
            'canAccessClassFeatures' => $this->dashboardService->canAccessClassFeatures($user),
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
            'marked_by' => $request->user()->teacher?->id ?? $request->user()->id,
        ]));

        return redirect()->route('teacher.dashboard')->with('status', 'Attendance recorded.');
    }
}
