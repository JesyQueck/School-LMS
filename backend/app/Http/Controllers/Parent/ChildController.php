<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user()->parentProfile;

        $children = $parent
            ? $parent->students()->with('class')->get()
            : collect();

        return view('parent.children', compact('children'));
    }

    public function show(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['class', 'reportCards' => function ($query) {
            $query->where('is_published', true)->with('term');
        }]);

        return view('parent.child', compact('student'));
    }

    public function timetable(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['class.classSubjects.subject', 'class.classSubjects.timetable' => function ($q) {
            $q->with('subject', 'teacher');
        }]);

        return view('parent.timetable', compact('student'));
    }
}
