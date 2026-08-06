<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;

class AcademicStructureController extends Controller
{
    public function index()
    {
        return view('admin.academic.index', [
            'sessions' => AcademicSession::with('terms')->get(),
            'subjects' => Subject::all(),
            'classSubjects' => ClassSubject::with(['class', 'subject'])->get(),
        ]);
    }

    public function createSession(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        AcademicSession::create($data);

        return redirect()->route('admin.academic')->with('status', 'Academic session created.');
    }

    public function createTerm(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        Term::create($data);

        return redirect()->route('admin.academic')->with('status', 'Term created.');
    }

    public function createSubject(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name'],
        ]);

        Subject::create($data);

        return redirect()->route('admin.academic')->with('status', 'Subject created.');
    }

    public function createClassSubject(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'is_compulsory' => ['nullable', 'boolean'],
        ]);

        ClassSubject::create($data);

        return redirect()->route('admin.academic')->with('status', 'Class subject created.');
    }
}
