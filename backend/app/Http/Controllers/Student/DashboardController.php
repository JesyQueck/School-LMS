<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Result;
use App\Models\Term;
use App\Services\ResultService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        $student->load(['class', 'results.classSubject.subject', 'attendance', 'fees.payments', 'reportCards' => function ($query) {
            $query->where('is_published', true)->with('term.academicSession');
        }]);

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();
        $publishedTermIds = $student->reportCards()->where('is_published', true)->pluck('term_id');

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

        $grade = (new ResultService())->calculateGrade($averageScore > 0 ? $averageScore : null)['grade'];

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
        $periods = collect([
            ['period' => 1, 'subject' => 'Mathematics', 'teacher' => 'Mr. Johnson'],
            ['period' => 2, 'subject' => 'English', 'teacher' => 'Ms. Smith'],
            ['period' => 3, 'subject' => 'Physics', 'teacher' => 'Dr. Brown'],
            ['period' => 1, 'subject' => 'Chemistry', 'teacher' => 'Dr. Lee'],
            ['period' => 2, 'subject' => 'Biology', 'teacher' => 'Dr. Adams'],
            ['period' => 3, 'subject' => 'Mathematics', 'teacher' => 'Mr. Johnson'],
        ]);

        $today = now()->format('l');
        $times = [
            1 => '8:00 AM – 8:45 AM',
            2 => '8:45 AM – 9:30 AM',
            3 => '9:30 AM – 10:15 AM',
        ];

        return $periods->where('period', '<=', 3)
            ->values()
            ->take(3)
            ->map(function ($p) use ($times) {
                $p['time'] = $times[$p['period']] ?? '';
                return $p;
            });
    }
}
