<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function __construct(
        protected ResultService $resultService,
    ) {}

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

        $this->resultService->createResult($data, $request->user());

        return redirect()->route('admin.results')->with('status', 'Result submitted.');
    }

    public function lock(Result $result)
    {
        $this->resultService->lockResult($result);

        return redirect()->route('admin.results')->with('status', 'Result locked.');
    }
}
