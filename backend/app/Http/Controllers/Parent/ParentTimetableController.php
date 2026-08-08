<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Carbon\Carbon;

class ParentTimetableController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $parent = $user->parent;
        $children = Student::whereIn('id', $parent->children ?? [])
            ->with(['class', 'user'])
            ->get();

        $timetableSlots = [
            ['day' => 'Monday', 'periods' => [
                ['time' => '8:00-9:00', 'subject' => 'Mathematics', 'teacher' => 'Mr. Smith'],
                ['time' => '9:00-10:00', 'subject' => 'English', 'teacher' => 'Ms. Johnson'],
                ['time' => '10:15-11:15', 'subject' => 'Physics', 'teacher' => 'Dr. Brown'],
                ['time' => '11:15-12:15', 'subject' => 'Chemistry', 'teacher' => 'Dr. Lee'],
            ]],
            ['day' => 'Tuesday', 'periods' => [
                ['time' => '8:00-9:00', 'subject' => 'Biology', 'teacher' => 'Dr. Adams'],
                ['time' => '9:00-10:00', 'subject' => 'Mathematics', 'teacher' => 'Mr. Smith'],
                ['time' => '10:15-11:15', 'subject' => 'English', 'teacher' => 'Ms. Johnson'],
                ['time' => '11:15-12:15', 'subject' => 'History', 'teacher' => 'Mr. Wilson'],
            ]],
            ['day' => 'Wednesday', 'periods' => [
                ['time' => '8:00-9:00', 'subject' => 'Chemistry', 'teacher' => 'Dr. Lee'],
                ['time' => '9:00-10:00', 'subject' => 'Biology', 'teacher' => 'Dr. Adams'],
                ['time' => '10:15-11:15', 'subject' => 'Mathematics', 'teacher' => 'Mr. Smith'],
                ['time' => '11:15-12:15', 'subject' => 'Geography', 'teacher' => 'Ms. Davis'],
            ]],
            ['day' => 'Thursday', 'periods' => [
                ['time' => '8:00-9:00', 'subject' => 'English', 'teacher' => 'Ms. Johnson'],
                ['time' => '9:00-10:00', 'subject' => 'Physics', 'teacher' => 'Dr. Brown'],
                ['time' => '10:15-11:15', 'subject' => 'Chemistry', 'teacher' => 'Dr. Lee'],
                ['time' => '11:15-12:15', 'subject' => 'Mathematics', 'teacher' => 'Mr. Smith'],
            ]],
            ['day' => 'Friday', 'periods' => [
                ['time' => '8:00-9:00', 'subject' => 'History', 'teacher' => 'Mr. Wilson'],
                ['time' => '9:00-10:00', 'subject' => 'Geography', 'teacher' => 'Ms. Davis'],
                ['time' => '10:15-11:15', 'subject' => 'Biology', 'teacher' => 'Dr. Adams'],
                ['time' => '11:15-12:15', 'subject' => 'English', 'teacher' => 'Ms. Johnson'],
            ]],
        ];

        return view('parent.timetable', compact('children', 'timetableSlots'));
    }
}
