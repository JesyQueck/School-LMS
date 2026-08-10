<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ParentProfile;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Services\FeeService;

class DashboardController extends Controller
{
    public function admin(FeeService $feeService)
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();
        $totalParents = ParentProfile::count();

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();
        $session = $currentTerm && $currentTerm->academicSession ? $currentTerm->academicSession->name : 'Current Session';
        $termName = $currentTerm ? $currentTerm->name : 'Current Term';

        $activeClasses = SchoolClass::count();
        $subjects = Subject::count();
        $teachersAssigned = Teacher::whereHas('classSubjectAssignments')->count();
        $totalTeachersForAssignment = $totalTeachers;

        $results = Result::all();
        $resultsSubmitted = $results->count();
        $resultsLocked = $results->where('is_locked', true)->count();
        $resultsPending = $results->where('is_locked', false)->count();

        $finance = $feeService->financeSummary();

        $recentActivity = AuditLog::with('user')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.index', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalParents',
            'session',
            'termName',
            'activeClasses',
            'subjects',
            'teachersAssigned',
            'totalTeachersForAssignment',
            'resultsSubmitted',
            'resultsLocked',
            'resultsPending',
            'finance',
            'recentActivity'
        ));
    }

    public function teacher()
    {
        return view('dashboard.teacher');
    }

    public function parent()
    {
        return view('dashboard.parent');
    }

    public function student()
    {
        return view('dashboard.student');
    }
}
