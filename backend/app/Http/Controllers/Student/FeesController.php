<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeesController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $fees = $student->fees()
            ->with(['feeType', 'term', 'payments'])
            ->latest()
            ->get();

        return view('student.fees', compact('student', 'fees'));
    }
}
