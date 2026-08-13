<?php

namespace App\Http\Controllers;

use App\Models\ClassAssignment;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Timetable;
use App\Models\Announcement;
use App\Models\Term;
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

        $subjectAssignments = collect();
        if ($teacher) {
            $subjectAssignments = TeacherClassSubject::with(['classSubject.class', 'classSubject.subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get();
        }

        $isClassTeacher = $teacher ? ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->exists() : false;

        $classAssignment = $isClassTeacher
            ? ClassAssignment::with(['class', 'term', 'academicSession'])
                ->where('teacher_id', $teacher->id)
                ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
                ->first()
            : null;

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();

        return view('dashboard.teacher', [
            'teacher' => $teacher,
            'subjectAssignments' => $subjectAssignments,
            'classAssignment' => $classAssignment,
            'isClassTeacher' => $isClassTeacher,
            'isSubjectTeacher' => $subjectAssignments->isNotEmpty(),
            'currentTerm' => $currentTerm,
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

                \App\Models\Attendance::updateOrCreate(
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
                \App\Models\Attendance::updateOrCreate(
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

        return view('teacher.attendance.class-attendance', [
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
            $students = \App\Models\Student::where('class_id', $activeClassAssignment->class_id)
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

    public function classes()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        $assignments = TeacherClassSubject::with(['classSubject.class', 'classSubject.subject'])
            ->where('teacher_id', $teacher?->id)
            ->where('is_active', true)
            ->get();

        return view('teacher.classes.index', [
            'assignments' => $assignments,
        ]);
    }

    public function classStudents(\App\Models\SchoolClass $class)
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        if (!$teacher) {
            abort(403);
        }

        $hasAccess = TeacherClassSubject::where('teacher_id', $teacher->id)
            ->whereHas('classSubject', fn ($q) => $q->where('class_id', $class->id))
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        $students = \App\Models\Student::where('class_id', $class->id)
            ->with(['user'])
            ->get();

        return view('teacher.students.index', [
            'class' => $class,
            'students' => $students,
        ]);
    }

    public function timetable()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        if (!$teacher) {
            abort(403);
        }

        $subjectAssignments = TeacherClassSubject::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('class_subject_id');

        $timetable = Timetable::with(['classSubject.subject', 'classSubject.class'])
            ->whereIn('class_subject_id', $subjectAssignments)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('teacher.timetable.index', [
            'subjectAssignmentIds' => $subjectAssignments,
            'timetable' => $timetable,
        ]);
    }

    public function profile()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        if (!$teacher) {
            abort(403);
        }

        $subjectAssignments = TeacherClassSubject::with(['classSubject.subject', 'classSubject.class'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        return view('teacher.profile.index', [
            'teacher' => $teacher,
            'subjectAssignments' => $subjectAssignments,
        ]);
    }

    public function results()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        $assignments = collect();
        if ($teacher) {
            $assignments = TeacherClassSubject::with(['classSubject.class', 'classSubject.subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->get();
        }

        return view('teacher.results.index', [
            'assignments' => $assignments,
        ]);
    }

    public function announcements()
    {
        $announcements = Announcement::where('target_audience', 'all')
            ->orWhere('target_audience', 'teacher')
            ->latest()
            ->get();

        return view('teacher.announcements.index', [
            'announcements' => $announcements,
        ]);
    }
}
