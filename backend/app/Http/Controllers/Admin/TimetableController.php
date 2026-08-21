<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Timetable;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    use AuditsActions;

    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function index(Request $request)
    {
        $classes = SchoolClass::with('classSubjects.subject')->get();
        $teachers = Teacher::with('user')->get();
        $terms = Term::whereHas('academicSession', function ($q) {
            $q->where('is_current', true);
        })->get();
        $sessions = AcademicSession::where('is_current', true)->get();

        $currentTerm = Term::where('is_current', true)
            ->with('academicSession')
            ->first();

        $sessionId = $request->input('session_id', $currentTerm?->academic_session_id);
        $termId = $request->input('term_id', $currentTerm?->id);
        $classId = $request->input('class_id');
        $teacherId = $request->input('teacher_id');

        $timetableQuery = Timetable::with([
            'classSubject.class',
            'classSubject.subject',
            'teacher.user',
            'term',
            'academicSession',
        ])
            ->orderByRaw("CASE day WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 WHEN 'Sunday' THEN 7 ELSE 8 END")
            ->orderBy('start_time');

        if ($termId) {
            $timetableQuery->where('term_id', $termId);
        }

        if ($sessionId) {
            $timetableQuery->where('academic_session_id', $sessionId);
        }

        if ($classId) {
            $timetableQuery->whereHas('classSubject.class', function ($q) use ($classId) {
                $q->where('id', $classId);
            });
        }

        if ($teacherId) {
            $timetableQuery->where('teacher_id', $teacherId);
        }

        $timetable = $timetableQuery->get();

        return view('admin.timetable.index', compact(
            'classes',
            'teachers',
            'terms',
            'sessions',
            'timetable',
            'currentTerm',
            'request'
        ));
    }

    public function create(Request $request)
    {
        $classes = SchoolClass::with('classSubjects.subject')->get();
        $teachers = Teacher::with('user')->get();
        $terms = Term::whereHas('academicSession', function ($q) {
            $q->where('is_current', true);
        })->get();
        $sessions = AcademicSession::where('is_current', true)->get();

        $currentTerm = Term::where('is_current', true)
            ->with('academicSession')
            ->first();

        return view('admin.timetable.create', compact(
            'classes',
            'teachers',
            'terms',
            'sessions',
            'currentTerm'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'day' => ['required', 'in:'.implode(',', self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
        ]);

        $timetable = Timetable::create($data);

        $this->audit($request, 'timetable.created', Timetable::class, $timetable->id, null, $data);

        return redirect()->route('admin.timetable.index')->with('status', 'Timetable entry created successfully.');
    }

    public function edit(Timetable $timetable)
    {
        $classes = SchoolClass::with('classSubjects.subject')->get();
        $teachers = Teacher::with('user')->get();
        $terms = Term::whereHas('academicSession', function ($q) {
            $q->where('is_current', true);
        })->get();
        $sessions = AcademicSession::where('is_current', true)->get();

        $currentTerm = Term::where('is_current', true)
            ->with('academicSession')
            ->first();

        $timetable->load(['classSubject.class', 'classSubject.subject', 'teacher.user', 'term', 'academicSession']);

        return view('admin.timetable.edit', compact(
            'timetable',
            'classes',
            'teachers',
            'terms',
            'sessions',
            'currentTerm'
        ));
    }

    public function update(Request $request, Timetable $timetable)
    {
        $data = $request->validate([
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'day' => ['required', 'in:'.implode(',', self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
        ]);

        $oldValue = $timetable->toArray();
        $timetable->update($data);

        $this->audit($request, 'timetable.updated', Timetable::class, $timetable->id, $oldValue, $timetable->toArray());

        return redirect()->route('admin.timetable.index')->with('status', 'Timetable entry updated successfully.');
    }

    public function destroy(Request $request, Timetable $timetable)
    {
        $this->audit($request, 'timetable.deleted', Timetable::class, $timetable->id, $timetable->toArray(), null);

        $timetable->delete();

        return redirect()->route('admin.timetable.index')->with('status', 'Timetable entry removed successfully.');
    }
}
