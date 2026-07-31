<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function index()
    {
        return view('admin.report-cards.index', [
            'reportCards' => ReportCard::with(['student', 'term'])->get(),
            'students' => Student::with('class')->get(),
            'terms' => Term::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_teacher_remark' => ['nullable', 'string'],
            'principal_remark' => ['nullable', 'string'],
            'position_in_class' => ['nullable', 'integer'],
            'total_students_in_class' => ['nullable', 'integer'],
            'next_term_begins' => ['nullable', 'date'],
        ]);

        ReportCard::create(array_merge($data, [
            'generated_at' => now(),
            'is_published' => false,
        ]));

        return redirect()->route('admin.report-cards')->with('status', 'Report card created.');
    }

    public function publish(ReportCard $reportCard)
    {
        $reportCard->update(['is_published' => true]);

        return redirect()->route('admin.report-cards')->with('status', 'Report card published.');
    }
}
