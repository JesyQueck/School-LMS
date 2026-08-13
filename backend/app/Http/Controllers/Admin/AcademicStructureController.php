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

        $session = AcademicSession::create($data);

        $this->createDefaultTerms($session);

        return redirect()->route('admin.academic')->with('status', 'Academic session and its 3 terms created.');
    }

    /**
     * Automatically create the standard 3 terms (First, Second, Third) for a session.
     *
     * Term dates are left blank so the admin can set the real dates later —
     * term dates vary each year and are edited on the academic structure page.
     */
    protected function createDefaultTerms(AcademicSession $session): void
    {
        foreach (['First Term', 'Second Term', 'Third Term'] as $name) {
            Term::create([
                'academic_session_id' => $session->id,
                'name' => $name,
                'start_date' => null,
                'end_date' => null,
            ]);
        }
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

    /**
     * Mark a session as the current academic session (only one at a time).
     */
    public function makeSessionCurrent(AcademicSession $session)
    {
        AcademicSession::where('is_current', true)->update(['is_current' => false]);
        $session->update(['is_current' => true]);

        return redirect()->route('admin.academic')->with('status', "{$session->name} is now the current session.");
    }

    /**
     * Mark a term as the current term (only one at a time).
     */
    public function makeTermCurrent(Term $term)
    {
        Term::where('is_current', true)->update(['is_current' => false]);
        $term->update(['is_current' => true]);

        return redirect()->route('admin.academic')->with('status', "{$term->name} is now the current term.");
    }

    /**
     * Update a term's start/end dates (set by the admin as the real dates become known).
     */
    public function updateTerm(Request $request, Term $term)
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $term->update($data);

        return redirect()->route('admin.academic')->with('status', "{$term->name} dates updated.");
    }
}
