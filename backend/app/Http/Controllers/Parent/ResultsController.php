<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    /**
     * Show a child's published results only.
     *
     * Results are visible to parents once the term's report card has been
     * published (CLAUDE.md publish control). Unpublished results are hidden.
     */
    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['class']);

        $publishedTerms = $student->reportCards()
            ->where('is_published', true)
            ->pluck('term_id');

        $results = $student->results()
            ->whereIn('term_id', $publishedTerms)
            ->with(['term', 'classSubject.subject'])
            ->get();

        return view('parent.results', compact('student', 'results'));
    }
}
