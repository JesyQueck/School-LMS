<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class FeesController extends Controller
{
    /**
     * Show a child's fee obligations, payments, and outstanding balances.
     */
    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['class']);

        $fees = $student->fees()
            ->with(['feeType', 'term', 'payments'])
            ->latest()
            ->get();

        return view('parent.fees', compact('student', 'fees'));
    }
}
