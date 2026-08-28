<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;

class ParentTimetableController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user()->parentProfile;
        $children = $parent
            ? $parent->students()->with(['schoolClass', 'user'])->get()
            : collect();

        $selectedId = $request->query('student');
        $selected = $children->firstWhere('id', $selectedId) ?? $children->first();

        $timetable = collect();
        $periods = collect();

        if ($selected) {
            $timetable = Timetable::with([
                'classSubject.subject',
                'classSubject.class',
                'teacher.user',
                'periodConfig.periods',
            ])
                ->whereHas('classSubject', function ($query) use ($selected) {
                    $query->where('class_id', $selected->class_id);
                })
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            if ($timetable->isNotEmpty()) {
                if ($timetable->first()->periodConfig) {
                    $periods = $timetable->first()->periodConfig->periods->sortBy('sort_order');
                } else {
                    $periods = $timetable->map(function ($entry) {
                        return (object) [
                            'name' => $entry->start_time->format('g:ia'),
                            'start_time' => $entry->start_time,
                            'end_time' => $entry->end_time,
                            'is_break' => false,
                            'sort_order' => 0,
                        ];
                    })->sortBy('start_time')->values();
                }
            }
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return view('parent.timetable', compact('children', 'selected', 'periods', 'timetable', 'days'));
    }
}
