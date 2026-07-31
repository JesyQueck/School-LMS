<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $announcements = Announcement::forRole('student')
            ->with('createdBy')
            ->latest()
            ->limit(5)
            ->get();

        return view('student.dashboard', compact('student', 'announcements'));
    }
}
