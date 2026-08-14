<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Models\Timetable;
use App\Services\TeacherDashboardService;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected TeacherDashboardService $dashboardService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $teacher = Teacher::where('user_id', $user->getKey())->first();

        $subjectAssignments = collect();

        if ($teacher) {
            $teacherId = $teacher->getKey();

            $subjectAssignments = TeacherClassSubject::with([
                'classSubject.class',
                'classSubject.subject',
            ])
                ->where('teacher_id', $teacherId)
                ->where('is_active', true)
                ->get();
        }

        $isClassTeacher = false;
        $classAssignment = null;

        if ($teacher) {
            $teacherId = $teacher->getKey();

            $isClassTeacher = ClassAssignment::where('teacher_id', $teacherId)
                ->whereHas('academicSession', function ($query) {
                    $query->where('is_current', true);
                })
                ->exists();

            if ($isClassTeacher) {
                $classAssignment = ClassAssignment::with([
                    'class',
                    'term',
                    'academicSession',
                ])
                    ->where('teacher_id', $teacherId)
                    ->whereHas('academicSession', function ($query) {
                        $query->where('is_current', true);
                    })
                    ->first();
            }
        }

        $currentTerm = Term::where('is_current', true)
            ->with('academicSession')
            ->first();

        return view('dashboard.teacher', [
            'teacher' => $teacher,
            'subjectAssignments' => $subjectAssignments,
            'classAssignment' => $classAssignment,
            'isClassTeacher' => $isClassTeacher,
            'isSubjectTeacher' => $subjectAssignments->isNotEmpty(),
            'currentTerm' => $currentTerm,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    public function storeAttendance(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'date' => ['required', 'date'],
            'status' => ['nullable'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        $classId = $validated['class_id'];
        $termId = $validated['term_id'];
        $date = $validated['date'];

        $classAssignment = ClassAssignment::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('term_id', $termId)
            ->whereHas('academicSession', function ($query) {
                $query->where('is_current', true);
            })
            ->first();

        if (! $classAssignment) {
            abort(403, 'You are not authorized to mark attendance for this class.');
        }

        $classStudentIds = Student::where('class_id', $classId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $statusData = $request->input('status');

        if (is_array($statusData)) {
            foreach ($statusData as $studentId => $status) {
                if (! is_numeric($studentId)) {
                    continue;
                }

                $studentId = (int) $studentId;

                if (! in_array($studentId, $classStudentIds, true)) {
                    abort(403, 'You are not authorized to mark attendance for this student.');
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'date' => $date,
                        'term_id' => $termId,
                    ],
                    [
                        'class_id' => $classId,
                        'status' => $status,
                        'marked_by' => $teacherId,
                    ]
                );
            }
        } else {
            $studentId = $request->input('student_id');
            $status = $statusData;

            if ($studentId && $status) {
                $studentId = (int) $studentId;

                if (! in_array($studentId, $classStudentIds, true)) {
                    abort(403, 'You are not authorized to mark attendance for this student.');
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'date' => $date,
                        'term_id' => $termId,
                    ],
                    [
                        'class_id' => $classId,
                        'status' => $status,
                        'marked_by' => $teacherId,
                    ]
                );
            }
        }

        $request->session()->forget('attendance_started_today_'.$date);

        $this->audit($request, 'attendance.marked', Attendance::class, null, null, [
            'class_id' => $classId,
            'term_id' => $termId,
            'date' => $date,
            'teacher_id' => $teacherId,
            'student_id' => $request->input('student_id'),
            'status' => $statusData,
        ]);

        return redirect()
            ->route('teacher.dashboard')
            ->with('status', 'Attendance recorded.');
    }

    public function classAttendance(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $classAssignments = ClassAssignment::with([
            'class',
            'term',
        ])
            ->where('teacher_id', $teacherId)
            ->whereHas('academicSession', function ($query) {
                $query->where('is_current', true);
            })
            ->get();

        $activeClassAssignment = $classAssignments->first();

        $students = collect();

        if ($activeClassAssignment) {
            $students = Student::where('class_id', $activeClassAssignment->class_id)
                ->with([
                    'user',
                    'fees',
                    'attendances' => function ($query) use ($activeClassAssignment) {
                        $query->where('term_id', $activeClassAssignment->term_id);
                    },
                ])
                ->get();
        }

        return view('teacher.attendance.class-attendance', [
            'classAssignment' => $activeClassAssignment,
            'students' => $students,
        ]);
    }

    public function attendance(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $classAssignments = ClassAssignment::with([
            'class',
            'term',
        ])
            ->where('teacher_id', $teacherId)
            ->whereHas('academicSession', function ($query) {
                $query->where('is_current', true);
            })
            ->get();

        $activeClassAssignment = $classAssignments->first();

        $students = collect();

        if ($activeClassAssignment) {
            $students = Student::where('class_id', $activeClassAssignment->class_id)
                ->with([
                    'user',
                    'fees',
                ])
                ->get();
        }

        $showAttendanceForm = $request->session()->has(
            'attendance_started_today_'.now()->toDateString()
        );

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

        $request->session()->put(
            'attendance_started_today_'.now()->toDateString(),
            true
        );

        return redirect()->route('teacher.attendance');
    }

    /*
    |--------------------------------------------------------------------------
    | Subjects
    |--------------------------------------------------------------------------
    */

    public function mySubjects(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $assignments = TeacherClassSubject::with([
            'classSubject.subject',
            'classSubject.class',
        ])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get();

        return view('teacher.assignments.index', [
            'assignments' => $assignments,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Classes
    |--------------------------------------------------------------------------
    */

    public function classes(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $assignments = TeacherClassSubject::with([
            'classSubject.class',
            'classSubject.subject',
        ])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get();

        return view('teacher.classes.index', [
            'assignments' => $assignments,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Class Students
    |--------------------------------------------------------------------------
    */

    public function classStudents(Request $request, SchoolClass $class)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();
        $classId = $class->getKey();

        $hasAccess = TeacherClassSubject::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->whereHas('classSubject', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->exists();

        if (! $hasAccess) {
            $hasAccess = ClassAssignment::where('teacher_id', $teacherId)
                ->where('class_id', $classId)
                ->whereHas('academicSession', function ($query) {
                    $query->where('is_current', true);
                })
                ->exists();
        }

        if (! $hasAccess) {
            abort(403, 'You are not authorized to view this class.');
        }

        $students = Student::where('class_id', $classId)
            ->with('user')
            ->get();

        return view('teacher.students.index', [
            'class' => $class,
            'students' => $students,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Timetable
    |--------------------------------------------------------------------------
    */

    public function timetable(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $subjectAssignmentIds = TeacherClassSubject::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->pluck('class_subject_id');

        $timetable = Timetable::with([
            'classSubject.subject',
            'classSubject.class',
        ])
            ->whereIn('class_subject_id', $subjectAssignmentIds)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return view('teacher.timetable.index', [
            'subjectAssignmentIds' => $subjectAssignmentIds,
            'timetable' => $timetable,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    public function profile(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $subjectAssignments = TeacherClassSubject::with([
            'classSubject.subject',
            'classSubject.class',
        ])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get();

        return view('teacher.profile.index', [
            'teacher' => $teacher,
            'subjectAssignments' => $subjectAssignments,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Results
    |--------------------------------------------------------------------------
    */

    public function results(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $assignments = TeacherClassSubject::with([
            'classSubject.class',
            'classSubject.subject',
        ])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get();

        return view('teacher.results.index', [
            'assignments' => $assignments,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Announcements
    |--------------------------------------------------------------------------
    */

    public function announcements(Request $request)
    {
        $announcements = Announcement::where(function ($query) {
            $query->where('target_role', 'all')
                ->orWhere('target_role', 'teacher');
        })
            ->latest()
            ->get();

        return view('teacher.announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    private function getTeacherOrFail(Request $request): Teacher
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $teacher = Teacher::where('user_id', $user->getKey())->first();

        if (! $teacher) {
            abort(403, 'Teacher profile not found.');
        }

        return $teacher;
    }
}
