<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Term;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    use AuditsActions;

    public function index()
    {
        return view('admin.assignments.index', [
            'teachers' => Teacher::with('user')->get(),
            'classes' => SchoolClass::with('formTeacher')->get(),
            'classSubjects' => ClassSubject::with(['class', 'subject'])->get(),
            'classAssignments' => ClassAssignment::with(['teacher.user', 'class', 'academicSession', 'term']),
            'assignments' => TeacherClassSubject::with(['teacher.user', 'classSubject.class', 'classSubject.subject']),
            'sessions' => AcademicSession::all(),
            'terms' => Term::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        TeacherClassSubject::create($data);

        $this->audit($request, 'subject_assignment.created', TeacherClassSubject::class, TeacherClassSubject::query()->latest('id')->value('id'), null, $data);

        return redirect()->route('admin.assignments')->with('status', 'Subject assignment created.');
    }

    public function storeClassAssignment(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
        ]);

        $assignment = ClassAssignment::create($data);

        $this->audit($request, 'class_assignment.created', ClassAssignment::class, $assignment->id, null, $data);

        return redirect()->route('admin.assignments')->with('status', 'Class assignment created.');
    }

    public function destroyClassAssignment($id)
    {
        $assignment = ClassAssignment::findOrFail($id);
        $assignment->delete();

        return redirect()->route('admin.assignments')->with('status', 'Class assignment removed.');
    }
}
