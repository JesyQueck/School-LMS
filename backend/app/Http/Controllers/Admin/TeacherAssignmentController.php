<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function index()
    {
        return view('admin.assignments.index', [
            'teachers' => Teacher::with('user')->get(),
            'classSubjects' => ClassSubject::with(['class', 'subject'])->get(),
            'assignments' => TeacherClassSubject::with(['teacher.user', 'classSubject.class', 'classSubject.subject'])->get(),
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

        return redirect()->route('admin.assignments')->with('status', 'Assignment created.');
    }
}
