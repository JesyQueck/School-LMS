<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user()->parentProfile;

        $children = $parent
            ? $parent->students()->with(['schoolClass', 'results.classSubject.subject', 'attendance', 'fees.payments'])->get()
            : collect();

        $children->each(function (Student $child) {
            $child->metrics = $this->computeMetrics($child);
        });

        $totalChildren = $children->count();
        $avgAttendance = $totalChildren > 0
            ? round($children->avg(fn (Student $c) => $c->metrics['attendance_rate']), 1)
            : 0;
        $avgResult = $totalChildren > 0
            ? round($children->avg(fn (Student $c) => $c->metrics['average_score']), 1)
            : 0;
        $feesDue = $children->sum(fn (Student $c) => $c->metrics['outstanding']);

        $stats = [
            'children' => $totalChildren,
            'attendance' => $avgAttendance,
            'avg_result' => $avgResult,
            'fees_due' => $feesDue,
        ];

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();

        $announcements = Announcement::forRole('parent')
            ->with('createdBy')
            ->latest()
            ->limit(5)
            ->get();

        return view('parent.dashboard', compact('children', 'stats', 'currentTerm', 'announcements'));
    }

    protected function computeMetrics(Student $child): array
    {
        $publishedTermIds = $child->reportCards()
            ->where('status', ReportCard::STATUS_PUBLISHED)
            ->pluck('term_id');

        $results = $child->results->whereIn('term_id', $publishedTermIds);
        $averageScore = $results->count() > 0
            ? round($results->avg(fn ($r) => $r->total ?? 0), 1)
            : 0;

        $records = $child->attendance;
        $present = $records->where('status', 'present')->count();
        $totalRecords = $records->count();
        $attendanceRate = $totalRecords > 0
            ? round(($present / $totalRecords) * 100, 1)
            : 0;

        $outstanding = $child->fees->sum(function ($fee) {
            $paid = $fee->payments->sum('amount_paid');

            return max(0, ($fee->amount_expected ?? 0) - $paid);
        });

        return [
            'average_score' => $averageScore,
            'attendance_rate' => $attendanceRate,
            'present' => $present,
            'total_records' => $totalRecords,
            'outstanding' => $outstanding,
        ];
    }
}
