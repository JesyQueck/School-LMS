<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ReportCard;
use App\Models\Term;
use App\Models\Timetable;
use App\Services\ResultService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        $student->load(['schoolClass', 'results.classSubject.subject', 'attendance', 'fees.payments', 'reportCards' => function ($query) {
            $query->where('status', ReportCard::STATUS_PUBLISHED)->with('term.academicSession');
        }]);

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();
        $publishedTermIds = $student->reportCards()->where('status', ReportCard::STATUS_PUBLISHED)->pluck('term_id');

        $results = $student->results->whereIn('term_id', $publishedTermIds);
        $averageScore = $results->count() > 0
            ? round($results->avg(fn ($r) => $r->total ?? 0), 1)
            : 0;
        $subjectCount = $results->count();

        $records = $student->attendance;
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $totalRecords = $records->count();
        $attendanceRate = $totalRecords > 0
            ? round(($present / $totalRecords) * 100, 1)
            : 0;

        $outstanding = $student->fees->sum(function ($fee) {
            $paid = $fee->payments->sum('amount_paid');

            return max(0, ($fee->amount_expected ?? 0) - $paid);
        });
        $totalExpected = $student->fees->sum(fn ($fee) => $fee->amount_expected ?? 0);
        $totalPaid = $student->fees->sum(fn ($fee) => $fee->payments->sum('amount_paid'));

        $grade = (new ResultService)->calculateGrade($averageScore > 0 ? $averageScore : null)['grade'];

        $latestReportCard = $student->reportCards->sortByDesc(fn ($rc) => $rc->term_id)->first();

        $announcements = Announcement::forRole('student')
            ->with('createdBy')
            ->latest()
            ->limit(5)
            ->get();

        $todayClasses = $this->todayClasses($student);

        return view('student.dashboard', compact(
            'student',
            'currentTerm',
            'publishedTermIds',
            'averageScore',
            'subjectCount',
            'present',
            'absent',
            'late',
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
