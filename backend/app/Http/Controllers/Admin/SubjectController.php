<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    use AuditsActions;

    public function index()
    {
        $subjects = Subject::with(['classSubjects.class'])->get();
        $classes = SchoolClass::all();
        $classSubjects = ClassSubject::with(['class', 'subject', 'teacherAssignments'])->get();

        return view('admin.subjects.index', compact('subjects', 'classes', 'classSubjects'));
    }

    public function create()
    {
        $classes = SchoolClass::all();

        return view('admin.subjects.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:classes,id'],
        ]);

        $subject = Subject::create([
            'name' => $data['name'],
        ]);

        foreach ($data['class_ids'] ?? [] as $classId) {
            ClassSubject::firstOrCreate([
                'class_id' => $classId,
                'subject_id' => $subject->id,
            ]);
        }

        $this->audit($request, 'subject.created', Subject::class, $subject->id, null, $data);

        return redirect()->route('admin.subjects.index')->with('status', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        $classes = SchoolClass::all();

        return view('admin.subjects.edit', compact('subject', 'classes'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name,'.$subject->id],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:classes,id'],
        ]);

        $oldValue = $subject->toArray();
        $subject->update(['name' => $data['name']]);
        $subject->updateClassSubjects($data['class_ids'] ?? []);
        $subject->load('classSubjects');

        $this->audit($request, 'subject.updated', Subject::class, $subject->id, $oldValue, $subject->toArray());

        return redirect()->route('admin.subjects.index')->with('status', 'Subject updated successfully.');
    }

    public function destroy(Request $request, Subject $subject)
    {
        $this->audit($request, 'subject.deleted', Subject::class, $subject->id, $subject->toArray(), null);
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('status', 'Subject removed successfully.');
    }
}
