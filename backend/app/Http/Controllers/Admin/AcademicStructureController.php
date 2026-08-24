<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class AcademicStructureController extends Controller
{
    use AuditsActions;

    public function index()
    {
        return view('admin.academic.index', [
            'sessions' => AcademicSession::with('terms')->get(),
            'subjects' => Subject::with('classSubjects.class')->get(),
            'classes' => SchoolClass::all(),
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

    public function createClassSubject(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'is_compulsory' => ['nullable', 'boolean'],
            'periods_per_week' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $data['is_compulsory'] = $data['is_compulsory'] ?? true;
        $data['periods_per_week'] = $data['periods_per_week'] ?? 1;

        ClassSubject::create($data);

        return redirect()->route('admin.academic')->with('status', 'Class subject created.');
    }

    public function updateClassSubject(Request $request, ClassSubject $classSubject)
    {
        $data = $request->validate([
            'is_compulsory' => ['nullable', 'boolean'],
            'periods_per_week' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $data['is_compulsory'] = $data['is_compulsory'] ?? $classSubject->is_compulsory;
        $data['periods_per_week'] = $data['periods_per_week'] ?? $classSubject->periods_per_week;

        $oldValue = $classSubject->toArray();
        $classSubject->update($data);

        $this->audit($request, 'class_subject.updated', ClassSubject::class, $classSubject->id, $oldValue, $classSubject->toArray());

        return redirect()->route('admin.academic')->with('status', 'Class-subject periods per week updated.');
    }

    /**
     * Remove a class-subject association.
     */
    public function destroyClassSubject(ClassSubject $classSubject)
    {
        $classSubject->delete();

        return redirect()->route('admin.academic')->with('status', 'Class-subject association removed.');
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
     * Update a session's name and dates.
     */
    public function updateSession(Request $request, AcademicSession $session)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $session->update($data);

        return redirect()->route('admin.academic')->with('status', "{$session->name} session updated.");
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
