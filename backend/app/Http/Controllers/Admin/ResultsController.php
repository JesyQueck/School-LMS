<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index()
    {
        return view('admin.results.index', [
            'results' => Result::with(['student', 'classSubject', 'term'])->get(),
            'students' => Student::with('class')->get(),
            'classSubjects' => ClassSubject::with(['class', 'subject'])->get(),
            'terms' => Term::all(),
            'classes' => SchoolClass::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_subject_id' => ['required', 'exists:class_subjects,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'ca_score' => ['nullable', 'numeric'],
            'exam_score' => ['nullable', 'numeric'],
            'remark' => ['nullable', 'string', 'max:255'],
        ]);

        $total = null;
        if (! empty($data['ca_score']) && ! empty($data['exam_score'])) {
            $total = (float) $data['ca_score'] + (float) $data['exam_score'];
        }

        $grade = $this->calculateGrade($total);

        Result::create(array_merge($data, [
            'total' => $total,
            'grade' => $grade,
            'submitted_by' => $request->user()->id,
        ]));

        return redirect()->route('admin.results')->with('status', 'Result submitted.');
    }

    public function lock(Result $result)
    {
        $result->update(['is_locked' => true]);

        return redirect()->route('admin.results')->with('status', 'Result locked.');
    }

    protected function calculateGrade($total): ?string
    {
        if ($total === null) {
            return null;
        }

        if ($total >= 70) {
            return 'A';
        }

        if ($total >= 60) {
            return 'B';
        }

        if ($total >= 50) {
            return 'C';
        }

        if ($total >= 40) {
            return 'D';
        }

        return 'F';
    }
}
