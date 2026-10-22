<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartAttendanceRequest;
use App\Http\Requests\StoreAttendanceRequest;
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
                'classSubject.class.students',
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
                    'class.students',
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

        $myClassIds = $subjectAssignments->pluck('classSubject.class_id')->unique()->filter();
        if ($isClassTeacher && $classAssignment) {
            $myClassIds->push($classAssignment->class_id);
        }
        $totalStudents = Student::whereIn('class_id', $myClassIds)->count();

        $todayClasses = Timetable::with(['classSubject.subject', 'classSubject.class'])
            ->join('class_subjects', 'timetables.class_subject_id', '=', 'class_subjects.id')
            ->whereIn('class_subjects.id', $subjectAssignments->pluck('class_subject_id'))
            ->where('timetables.day', now()->format('l'))
            ->orderBy('timetables.start_time')
            ->select('timetables.*')
            ->get();

        $todayAttendanceRecords = collect();
        if ($todayClasses->isNotEmpty()) {
            $todayClassIds = $todayClasses->pluck('classSubject.class_id')->unique();
            $todayAttendanceRecords = Attendance::whereIn('class_id', $todayClassIds)
                ->whereDate('date', now()->toDateString())
                ->get();
        }
        $totalTodayAttendance = $todayAttendanceRecords->where('status', 'present')->count();
        $todayAttendanceRate = $totalStudents > 0 ? round(($totalTodayAttendance / max($totalStudents, 1)) * 100) : 0;

        $formClassAttendanceRate = 0;
        if ($isClassTeacher && $classAssignment) {
            $formClassStudentCount = Student::where('class_id', $classAssignment->class_id)->count();
            if ($formClassStudentCount > 0) {
                $formClassAttendanceRecords = Attendance::where('class_id', $classAssignment->class_id)
                    ->whereDate('date', now()->toDateString())
                    ->get();
                $presentCount = $formClassAttendanceRecords->where('status', 'present')->count();
                $formClassAttendanceRate = round(($presentCount / $formClassStudentCount) * 100);
            }
        }
        $formClassAttendanceRateValue = $isClassTeacher ? $formClassAttendanceRate.'%' : $todayAttendanceRate.'%';

        $recentAnnouncements = Announcement::forRole('teacher')
            ->latest()
            ->limit(5)
            ->get();

        $myClasses = $subjectAssignments->unique('classSubject.class_id')->values();

        return view('dashboard.teacher', [
            'user' => $user,
            'teacher' => $teacher,
            'subjectAssignments' => $subjectAssignments,
            'classAssignment' => $classAssignment,
            'isClassTeacher' => $isClassTeacher,
            'isSubjectTeacher' => $subjectAssignments->isNotEmpty(),
            'currentTerm' => $currentTerm,
            'totalStudents' => $totalStudents,
            'todayClasses' => $todayClasses,
            'todayAttendanceRate' => $todayAttendanceRate,
            'totalTodayAttendance' => $totalTodayAttendance,
            'formClassAttendanceRateValue' => $formClassAttendanceRateValue,
            'recentAnnouncements' => $recentAnnouncements,
            'myClasses' => $myClasses,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    public function storeAttendance(StoreAttendanceRequest $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $validated = $request->validated();

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

        $request->session()->put('attendance_marked_today_'.$termId.'_'.$classId, $date);

        $this->audit($request, 'attendance.marked', Attendance::class, null, null, [
            'class_id' => $classId,
            'term_id' => $termId,
            'date' => $date,
            'teacher_id' => $teacherId,
            'student_id' => $request->input('student_id'),
            'status' => $statusData,
        ]);

        return redirect()
            ->route('teacher.attendance')
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
        $todayAttendances = collect();

        if ($activeClassAssignment) {
            $students = Student::where('class_id', $activeClassAssignment->class_id)
                ->with([
                    'user',
                    'fees',
                ])
                ->get();

            $today = now()->toDateString();
            $todayAttendances = Attendance::where('class_id', $activeClassAssignment->class_id)
                ->where('term_id', $activeClassAssignment->term_id)
                ->whereDate('date', $today)
                ->with('student')
                ->get();
        }

        $termId = $activeClassAssignment->term_id ?? null;
        $classId = $activeClassAssignment->class_id ?? null;
        $todayKey = 'attendance_marked_today_'.$termId.'_'.$classId;
        $startedKey = 'attendance_started_today_'.$termId.'_'.$classId;

        // "Marked" is date-stamped; a new day automatically resets the UI to
        // "Take Attendance". Overview takes priority over the form on the same day.
        // After clicking "Edit", the session flag is cleared so the form shows again.
        // If the session was lost (e.g., after logout/login), fall back to checking
        // whether attendance records exist in the database for today.
        $markedDate = $request->session()->get($todayKey);
        $hasSessionFlag = $markedDate === now()->toDateString();
        $showOverview = $hasSessionFlag || (! $request->session()->has($startedKey) && $todayAttendances->isNotEmpty());
        $showAttendanceForm = ! $showOverview && $request->session()->has($startedKey);

        return view('teacher.attendance.index', [
            'classAssignment' => $activeClassAssignment,
            'students' => $students,
            'todayAttendances' => $todayAttendances,
            'showAttendanceForm' => $showAttendanceForm,
            'showOverview' => $showOverview,
        ]);
    }

    public function startAttendance(StartAttendanceRequest $request)
    {
        $validated = $request->validated();

        // Date-stamp the "started" flag so a new day automatically resets the UI
        // back to the "Take Attendance" prompt.
        $request->session()->put(
            'attendance_started_today_'.$validated['term_id'].'_'.$validated['class_id'],
            now()->toDateString()
        );

        return redirect()->route('teacher.attendance');
    }

    public function editAttendance(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);

        $classAssignment = ClassAssignment::with('class', 'term')
            ->where('teacher_id', $teacher->getKey())
            ->whereHas('academicSession', function ($query) {
                $query->where('is_current', true);
            })
            ->first();

        if (! $classAssignment) {
            abort(403, 'You are not authorized to mark attendance.');
        }

        // Clear the "marked today" flag so the marking form is shown again.
        // The "started" flag is kept, so the flow returns to the form.
        $request->session()->forget('attendance_marked_today_'.$classAssignment->term_id.'_'.$classAssignment->class_id);

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

        $isClassTeacher = ClassAssignment::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->exists();

        $students = Student::where('class_id', $classId)
            ->with('user')
            ->get();

        return view('teacher.students.index', [
            'class' => $class,
            'students' => $students,
            'isClassTeacher' => $isClassTeacher,
        ]);
    }

    public function myStudents(Request $request)
    {
        $teacher = $this->getTeacherOrFail($request);
        $teacherId = $teacher->getKey();

        $classAssignment = ClassAssignment::where('teacher_id', $teacherId)
            ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
            ->with('class')
            ->first();

        if (! $classAssignment) {
            abort(403, 'My Students is only available for class teachers.');
        }

        $students = Student::where('class_id', $classAssignment->class_id)
            ->with('user')
            ->get();

        return view('teacher.students.my-students', [
            'teacher' => $teacher,
            'students' => $students,
            'classAssignment' => $classAssignment,
        ]);
    }

    public function studentProfile(Request $request, Student $student)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            $teacher = $this->getTeacherOrFail($request);
            $teacherId = $teacher->getKey();

            $hasAccess = TeacherClassSubject::where('teacher_id', $teacherId)
                ->where('is_active', true)
                ->whereHas('classSubject', function ($query) use ($student) {
                    $query->where('class_id', $student->class_id);
                })
                ->exists();

            if (! $hasAccess) {
                $hasAccess = ClassAssignment::where('teacher_id', $teacherId)
                    ->where('class_id', $student->class_id)
                    ->whereHas('academicSession', fn ($q) => $q->where('is_current', true))
                    ->exists();
            }

            if (! $hasAccess) {
                abort(403, 'You are not authorized to view this student.');
            }
        }

        $student->load(['user', 'schoolClass', 'results.classSubject.subject', 'reportCards']);

        return view('teacher.students.show', [
            'student' => $student,
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
            'periodConfig.periods',
        ])
            ->whereIn('class_subject_id', $subjectAssignmentIds)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $periods = collect();
        if ($timetable->isNotEmpty()) {
            if ($timetable->first()->periodConfig) {
                $periods = $timetable->first()->periodConfig->periods->sortBy('sort_order');
            } else {
                $periods = $timetable->map(function ($entry) {
                    return (object) [
                        'name' => $entry->start_time->format('g:ia'),
                        'start_time' => $entry->start_time,
                        'end_time' => $entry->end_time,
                        'is_break' => false,
                        'sort_order' => 0,
                    ];
                })->sortBy('start_time')->values();
            }
        }

        return view('teacher.timetable.index', [
            'subjectAssignmentIds' => $subjectAssignmentIds,
            'timetable' => $timetable,
            'periods' => $periods,
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
