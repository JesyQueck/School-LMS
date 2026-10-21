<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\ParentProfile;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use Carbon\Carbon;
use App\Services\FeeService;

class DashboardController extends Controller
{
    public function admin(FeeService $feeService)
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();
        $totalParents = ParentProfile::count();

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();
        $session = $currentTerm && $currentTerm->academicSession ? $currentTerm->academicSession->name : 'Current Session';
        $termName = $currentTerm ? $currentTerm->name : 'Current Term';

        $activeClasses = SchoolClass::count();
        $subjects = Subject::count();
        $teachersAssigned = Teacher::whereHas('classSubjectAssignments')->count();
        $totalTeachersForAssignment = $totalTeachers;

        $resultMetrics = null;
        if ($currentTerm) {
            $termResults = Result::where('term_id', $currentTerm->id)
                ->selectRaw('COUNT(*) as recorded, SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked')
                ->first();

            $resultMetrics = [
                'recorded' => (int) $termResults->recorded,
                'locked' => (int) $termResults->locked,
                'unlocked' => (int) $termResults->recorded - (int) $termResults->locked,
            ];
        }

        $finance = $feeService->financeSummary();

        $attendanceChart = ['labels' => [], 'datasets' => []];
        if ($currentTerm) {
            $attendanceRows = Attendance::query()
                ->where('term_id', $currentTerm->id)
                ->select(['date', 'status'])
                ->selectRaw('COUNT(*) as total')
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get();

            $dates = $attendanceRows->pluck('date')
                ->map(fn ($date) => Carbon::parse($date)->format('M j, Y'))
                ->unique()
                ->values();
            $statuses = $attendanceRows->pluck('status')
                ->map(fn ($status) => strtolower((string) $status))
                ->unique()
                ->sortBy(fn ($status) => match ($status) {
                    'present' => 0,
                    'absent' => 1,
                    'late' => 2,
                    default => 3,
                })
                ->values();

            $attendanceChart['labels'] = $dates->all();
            $attendanceChart['datasets'] = $statuses->map(function ($status) use ($attendanceRows, $dates) {
                $countsByDate = $attendanceRows
                    ->filter(fn ($row) => strtolower((string) $row->status) === $status)
                    ->mapWithKeys(fn ($row) => [
                        Carbon::parse($row->date)->format('M j, Y') => (int) $row->total,
                    ]);

                return [
                    'label' => ucfirst($status),
                    'data' => $dates->map(fn ($date) => $countsByDate->get($date, 0))->all(),
                ];
            })->all();
        }

        $recentActivity = AuditLog::with('user')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.index', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalParents',
            'session',
            'termName',
            'currentTerm',
            'activeClasses',
            'subjects',
            'teachersAssigned',
            'totalTeachersForAssignment',
            'resultMetrics',
            'finance',
            'attendanceChart',
            'recentActivity'
        ));
    }

    public function teacher()
    {
        return view('dashboard.teacher');
    }

    public function parent()
    {
        return view('dashboard.parent');
    }

    public function student()
    {
        return view('dashboard.student');
    }
}
