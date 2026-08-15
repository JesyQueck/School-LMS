<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)
            ->with(['schoolClass', 'user'])
            ->first();

        return view('student.profile', compact('student', 'user'));
    }
}
