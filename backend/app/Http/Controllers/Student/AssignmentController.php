<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();

        $assignments = collect([
            (object)['title' => 'Mathematics Essay', 'subject' => 'Mathematics', 'teacher' => 'Mr. Johnson', 'type' => 'essay', 'due_date' => Carbon::now()->addDays(2)],
            (object)['title' => 'Science Project', 'subject' => 'Biology', 'teacher' => 'Ms. Adams', 'type' => 'project', 'due_date' => Carbon::now()->subDays(1)],
            (object)['title' => 'English Comprehension', 'subject' => 'English', 'teacher' => 'Ms. Smith', 'type' => 'reading', 'due_date' => Carbon::now()->addDays(5)],
        ]);

        $overdue = $assignments->filter(fn($a) => Carbon::parse($a->due_date)->isPast());
        $upcoming = $assignments->filter(fn($a) => !Carbon::parse($a->due_date)->isPast());

        return view('student.assignments', compact('assignments', 'overdue', 'upcoming', 'student'));
    }
}
