<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    public function show(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load([
            'schoolClass',
            'academicSession',
            'user',
            'results.classSubject.subject',
            'attendance',
            'fees.payments',
            'reportCards' => function ($query) {
                $query->where('status', ReportCard::STATUS_PUBLISHED)->with('term');
            },
            'contacts',
            'emergencyContacts',
            'documents',
        ]);

        $publishedTermIds = $student->reportCards()
            ->where('status', ReportCard::STATUS_PUBLISHED)
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

        $totalExpected = 0;
        $totalPaid = 0;
        $outstanding = 0;

        foreach ($student->fees as $fee) {
            $expected = $fee->amount_expected ?? 0;
            $paid = $fee->payments->sum('amount_paid');
            $totalExpected += $expected;
            $totalPaid += $paid;
            $outstanding += max(0, $expected - $paid);
        }

        $metrics = [
            'average_score' => $averageScore,
            'attendance_rate' => $attendanceRate,
            'present' => $present,
            'total_records' => $totalRecords,
            'total_expected' => $totalExpected,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
        ];

        $reportCardTermIds = ReportCard::where('student_id', $student->id)
            ->where('status', ReportCard::STATUS_PUBLISHED)
            ->pluck('term_id')
            ->unique();

        $termsQuery = Term::query();

        if ($student->academic_session_id) {
            $termsQuery->where('academic_session_id', $student->academic_session_id);
        }

        if ($reportCardTermIds->isNotEmpty()) {
            $termsQuery->orWhereIn('id', $reportCardTermIds);
        }

        $terms = $termsQuery
            ->orderBy('id')
            ->with(['reportCards' => function ($query) use ($student) {
                $query->where('student_id', $student->id)->where('status', ReportCard::STATUS_PUBLISHED);
            }])
            ->get();

        return view('parent.child', compact('student', 'metrics', 'terms'));
    }
}
