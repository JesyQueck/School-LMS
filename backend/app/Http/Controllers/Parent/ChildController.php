<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    public function show(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load([
            'class',
            'results.classSubject.subject',
            'attendance',
            'fees.payments',
            'reportCards' => function ($query) {
                $query->where('is_published', true)->with('term');
            },
        ]);

        $publishedTermIds = $student->reportCards()
            ->where('is_published', true)
            ->pluck('term_id');

        $results = $student->results->whereIn('term_id', $publishedTermIds);
        $averageScore = $results->count() > 0
            ? round($results->avg(fn ($r) => $r->total ?? 0), 1)
            : 0;

        $records = $student->attendance;
        $present = $records->where('status', 'present')->count();
        $totalRecords = $records->count();
        $attendanceRate = $totalRecords > 0
            ? round(($present / $totalRecords) * 100, 1)
            : 0;

        $outstanding = $student->fees->sum(function ($fee) {
            $paid = $fee->payments->sum('amount_paid');
            return max(0, ($fee->amount_expected ?? 0) - $paid);
        });

        $metrics = [
            'average_score' => $averageScore,
            'attendance_rate' => $attendanceRate,
            'present' => $present,
            'total_records' => $totalRecords,
            'outstanding' => $outstanding,
        ];

        $terms = Term::orderBy('id')->with(['reportCards' => function ($query) use ($student) {
            $query->where('student_id', $student->id);
        }])->get();

        return view('parent.child', compact('student', 'metrics', 'terms'));
    }
}
