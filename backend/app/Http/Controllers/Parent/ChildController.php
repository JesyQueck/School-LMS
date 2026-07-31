<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    public function show(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['class', 'user', 'reportCards' => function ($query) {
            $query->where('is_published', true)->with('term');
        }]);

        return view('parent.child', compact('student'));
    }
}
