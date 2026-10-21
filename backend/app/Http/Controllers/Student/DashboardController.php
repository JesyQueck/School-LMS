<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ReportCard;
use App\Models\Term;
use App\Models\Timetable;
use App\Services\ResultService;
use App\Services\PublishedResultTrendService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, PublishedResultTrendService $trendService)
    {
        $student = $request->user()->student;

        abort_if(! $student, 403, 'Student profile not found.');

        $student->load(['schoolClass', 'results.classSubject.subject', 'attendance', 'fees.payments', 'reportCards' => function ($query) {
            $query->where('status', ReportCard::STATUS_PUBLISHED)->with('term.academicSession');
        }]);

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();
        $publishedTermIds = $student->reportCards()->where('status', ReportCard::STATUS_PUBLISHED)->pluck('term_id');

        $publishedResults = $student->results
            ->whereIn('term_id', $publishedTermIds)
            ->filter(fn ($result) => $result->total !== null)
            ->values();
        $averageScore = $publishedResults->isNotEmpty()
            ? round($publishedResults->avg('total'), 1)
            : null;
        $resultTrend = $trendService->forStudent($student);
        $subjectCount = $publishedResults
            ->map(fn ($result) => $result->classSubject?->subject_id)
            ->filter()
            ->unique()
            ->count();

        $records = $student->attendance;
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $totalRecords = $records->count();
        $attendanceRate = $totalRecords > 0
            ? round(($present / $totalRecords) * 100, 1)
            : null;

        $outstanding = $student->fees->sum(function ($fee) {
            $paid = $fee->payments->sum('amount_paid');

            return max(0, ($fee->amount_expected ?? 0) - $paid);
        });
        $totalExpected = $student->fees->sum(fn ($fee) => $fee->amount_expected ?? 0);
        $totalPaid = $student->fees->sum(fn ($fee) => $fee->payments->sum('amount_paid'));

        $grade = $averageScore !== null
            ? (new ResultService)->calculateGrade($averageScore)['grade']
            : null;

        $latestReportCard = ReportCard::query()
            ->where('student_id', $student->id)
            ->where('status', ReportCard::STATUS_PUBLISHED)
            ->join('terms', 'report_cards.term_id', '=', 'terms.id')
            ->leftJoin('academic_sessions', 'terms.academic_session_id', '=', 'academic_sessions.id')
            ->select('report_cards.*')
            ->with('term.academicSession')
            ->orderByDesc('academic_sessions.start_date')
            ->orderByDesc('terms.start_date')
            ->orderByDesc('report_cards.id')
            ->first();

        $announcements = Announcement::forRole('student')
            ->with('createdBy')
            ->latest()
            ->limit(5)
            ->get();

        $todayClasses = $this->todayClasses($student);

        return view('student.dashboard', compact(
            'student',
            'currentTerm',
            'publishedResults',
            'resultTrend',
            'averageScore',
            'subjectCount',
            'present',
            'absent',
            'late',
            'totalRecords',
            'attendanceRate',
            'outstanding',
            'totalExpected',
            'totalPaid',
            'grade',
            'latestReportCard',
            'announcements',
            'todayClasses'
        ));
    }

    protected function todayClasses($student)
    {
        $today = now()->format('l');

        $timetable = Timetable::with([
            'classSubject.subject',
            'teacher.user',
        ])
            ->whereHas('classSubject.class', function ($query) use ($student) {
                $query->where('id', $student->class_id);
            })
            ->where('day', $today)
            ->orderBy('start_time')
            ->get();

        return $timetable->map(function (Timetable $entry, $index) {
            $startTime = Carbon::parse($entry->start_time)->format('g:i A');
            $endTime = Carbon::parse($entry->end_time)->format('g:i A');

            $periodNumber = $index + 1;

            return [
                'period' => $periodNumber,
                'subject' => $entry->classSubject->subject->name ?? 'Unknown Subject',
                'teacher' => $entry->teacher && $entry->teacher->user
                    ? $entry->teacher->user->name
                    : 'Not assigned',
                'time' => $startTime.' – '.$endTime,
            ];
        });
    }
}
