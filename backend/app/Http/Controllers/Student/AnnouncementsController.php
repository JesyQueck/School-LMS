<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementsController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::forRole('student')
            ->with('createdBy')
            ->latest()
            ->paginate(15);

        return view('student.announcements', compact('announcements'));
    }
}
