<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Term;
use App\Services\PublishedResultTrendService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index(Request $request, PublishedResultTrendService $trendService)
    {
        $parent = $request->user()->parentProfile;

        $children = $parent
            ? $parent->students()->with([
                'schoolClass',
                'results.classSubject.subject',
                'attendance',
                'fees.payments',
                'reportCards' => fn ($query) => $query
                    ->where('status', ReportCard::STATUS_PUBLISHED)
                    ->with('term.academicSession')
                    ->join('terms', 'report_cards.term_id', '=', 'terms.id')
                    ->leftJoin('academic_sessions', 'terms.academic_session_id', '=', 'academic_sessions.id')
                    ->select('report_cards.*')
                    ->orderByDesc('academic_sessions.start_date')
                    ->orderByDesc('terms.start_date')
                    ->orderByDesc('report_cards.id'),
            ])->orderBy('students.id')->get()
            : collect();

        $filters = $request->validate([
            'student' => ['nullable', 'integer', Rule::in($children->modelKeys())],
        ]);
        $selectedChild = $children->firstWhere('id', $filters['student'] ?? null)
            ?? $children->first();

        $children->each(function (Student $child) {
            $child->metrics = $this->computeMetrics($child);
        });

        $totalChildren = $children->count();
        $childrenWithAttendance = $children->filter(fn (Student $child) => $child->metrics['attendance_rate'] !== null);
        $childrenWithResults = $children->filter(fn (Student $child) => $child->metrics['average_score'] !== null);
        $avgAttendance = $childrenWithAttendance->isNotEmpty()
            ? round($childrenWithAttendance->avg(fn (Student $child) => $child->metrics['attendance_rate']), 1)
            : null;
        $avgResult = $childrenWithResults->isNotEmpty()
            ? round($childrenWithResults->avg(fn (Student $child) => $child->metrics['average_score']), 1)
            : null;
        $feesDue = $children->sum(fn (Student $c) => $c->metrics['outstanding']);

        $stats = [
            'children' => $totalChildren,
            'attendance' => $avgAttendance,
            'attendance_children' => $childrenWithAttendance->count(),
            'avg_result' => $avgResult,
            'result_children' => $childrenWithResults->count(),
            'fees_due' => $feesDue,
        ];

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();

        $announcements = Announcement::forRole('parent')
            ->with('createdBy')
            ->latest()
            ->limit(5)
            ->get();

        $recentResults = collect();
        $resultTrend = ['labels' => [], 'averages' => [], 'rows' => []];
        if ($selectedChild) {
            $publishedTermIds = $selectedChild->reportCards->pluck('term_id');
            $recentResults = $selectedChild->results()
                ->whereIn('term_id', $publishedTermIds)
                ->join('terms', 'results.term_id', '=', 'terms.id')
                ->leftJoin('academic_sessions', 'terms.academic_session_id', '=', 'academic_sessions.id')
                ->select('results.*')
                ->with(['term', 'classSubject.subject'])
                ->orderByDesc('academic_sessions.start_date')
                ->orderByDesc('terms.start_date')
                ->orderByDesc('results.id')
                ->limit(5)
                ->get();
            $resultTrend = $trendService->forStudent($selectedChild);
        }

        $recentReportCards = $children
            ->map(fn (Student $child) => $child->reportCards->first()
                ? ['student' => $child, 'reportCard' => $child->reportCards->first()]
                : null)
            ->filter()
            ->sortByDesc(fn (array $entry) => [
                $entry['reportCard']->term?->academicSession?->start_date?->format('Ymd') ?? '',
                $entry['reportCard']->term?->start_date?->format('Ymd') ?? '',
                $entry['reportCard']->id,
                $entry['student']->id,
            ])
            ->values();

        return view('parent.dashboard', compact(
            'children',
            'selectedChild',
            'recentResults',
            'resultTrend',
            'recentReportCards',
            'stats',
            'currentTerm',
            'announcements',
        ));
    }

    protected function computeMetrics(Student $child): array
    {
        $publishedTermIds = $child->reportCards()
            ->where('status', ReportCard::STATUS_PUBLISHED)
            ->pluck('term_id');

        $results = $child->results
            ->whereIn('term_id', $publishedTermIds)
            ->filter(fn ($result) => $result->total !== null);
        $averageScore = $results->isNotEmpty()
            ? round($results->avg('total'), 1)
            : null;

        $records = $child->attendance;
        $present = $records->where('status', 'present')->count();
        $totalRecords = $records->count();
        $attendanceRate = $totalRecords > 0
            ? round(($present / $totalRecords) * 100, 1)
            : null;

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
