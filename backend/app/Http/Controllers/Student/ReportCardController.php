<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $reportCards = $student->reportCards()
            ->where('is_published', true)
            ->with('term')
            ->latest()
            ->get();

        return view('student.report-cards', compact('student', 'reportCards'));
    }
}
