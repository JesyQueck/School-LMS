<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Student;
use App\Services\TeacherDashboardService;
use Illuminate\Http\Request;

use App\Traits\AuditsActions;

class TeacherPortalController extends Controller
{
    use AuditsActions;
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
        $teacher = $request->user()->teacher;

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'date' => ['required', 'date'],
        ]);

        $class_id = $validated['class_id'];
        $term_id = $validated['term_id'];
        $date = $validated['date'];

        $statusData = $request->input('status');

        if (is_array($statusData)) {
            foreach ($statusData as $student_id => $status) {
                if (!is_numeric($student_id)) {
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $student_id,
                        'date' => $date,
                    ],
                    [
                        'class_id' => $class_id,
                        'term_id' => $term_id,
                        'status' => $status,
                        'marked_by' => $teacher?->id ?? $request->user()->id,
                    ]
                );
            }
        } else {
            $student_id = $request->input('student_id');
            $status = $statusData;

            if ($student_id && $status) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $student_id,
                        'date' => $date,
                    ],
                    [
                        'class_id' => $class_id,
                        'term_id' => $term_id,
                        'status' => $status,
                        'marked_by' => $teacher?->id ?? $request->user()->id,
                    ]
                );
            }
        }

        $request->session()->forget('attendance_started_today_' . $date);

        $this->audit($request, 'attendance.marked', \App\Models\Attendance::class, null, null, [
            'class_id' => $class_id,
            'term_id' => $term_id,
            'date' => $date,
            'student_id' => $student_id ?? null,
            'status' => $statusData,
        ]);

        return redirect()->route('teacher.dashboard')->with('status', 'Attendance recorded.');
    }

    public function classAttendance(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        $classAssignments = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher?->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->get();

        $activeClassAssignment = $classAssignments->first();

        $students = collect();
        if ($activeClassAssignment) {
            $students = \App\Models\Student::where('class_id', $activeClassAssignment->class_id)
                ->with(['user', 'fees', 'attendances' => fn($q) => $q->where('term_id', $activeClassAssignment->term_id)])
                ->get();
        }

        return view('teacher.my-class.index', [
            'classAssignment' => $activeClassAssignment,
            'students' => $students,
        ]);
    }

    public function attendance(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        $classAssignments = ClassAssignment::with(['class', 'term'])
            ->where('teacher_id', $teacher?->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->get();

        $activeClassAssignment = $classAssignments->first();

        $students = collect();
        if ($activeClassAssignment) {
            $students = Student::where('class_id', $activeClassAssignment->class_id)
                ->with(['user', 'fees'])
                ->get();
        }

        $showAttendanceForm = $request->session()->has('attendance_started_today_' . now()->toDateString());

        return view('teacher.attendance.index', [
            'classAssignment' => $activeClassAssignment,
            'students' => $students,
            'showAttendanceForm' => $showAttendanceForm,
        ]);
    }

    public function startAttendance(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'term_id' => ['required', 'exists:terms,id'],
        ]);

        $request->session()->put('attendance_started_today_' . now()->toDateString(), true);

        return redirect()->route('teacher.attendance');
    }

    public function mySubjects()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        $assignments = TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
            ->where('teacher_id', $teacher?->id)
            ->where('is_active', true)
            ->get();

        return view('teacher.assignments.index', [
            'assignments' => $assignments,
        ]);
    }
}
