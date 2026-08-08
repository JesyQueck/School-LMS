<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $publishedTerms = $student->reportCards()
            ->where('is_published', true)
            ->pluck('term_id');

        $results = $student->results()
            ->whereIn('term_id', $publishedTerms)
            ->with(['term', 'classSubject.subject'])
            ->get();

        return view('student.results', compact('student', 'results'));
    }
}
