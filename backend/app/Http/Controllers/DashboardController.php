<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Term;
use App\Services\AuditLogFormatter;
use App\Services\AdminDashboardAnalytics;
use Carbon\CarbonImmutable;
use App\Services\FeeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function admin(Request $request, FeeService $feeService, AdminDashboardAnalytics $analytics, AuditLogFormatter $auditFormatter)
    {
        $classes = SchoolClass::query()->orderBy('name')->get(['id', 'name']);
        $currentTerm = Term::where('is_current', true)
            ->with('academicSession')
            ->orderByDesc('id')
            ->first();

        $dateRules = ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.now()->toDateString()];
        if ($currentTerm?->start_date) {
            $dateRules[] = 'after_or_equal:'.$currentTerm->start_date->toDateString();
        }
        if ($currentTerm?->end_date) {
            $dateRules[] = 'before_or_equal:'.$currentTerm->end_date->toDateString();
        }

        $filters = $request->validate([
            'class_id' => ['nullable', 'integer', Rule::in($classes->modelKeys())],
            'date' => $dateRules,
        ]);
        $selectedClassId = isset($filters['class_id']) ? (int) $filters['class_id'] : null;
        $selectedDate = isset($filters['date']) ? CarbonImmutable::parse($filters['date']) : null;

        if ($currentTerm && ! $selectedDate) {
            $today = CarbonImmutable::today();
            $todayInTerm = (! $currentTerm->start_date || $today->greaterThanOrEqualTo($currentTerm->start_date->startOfDay()))
                && (! $currentTerm->end_date || $today->lessThanOrEqualTo($currentTerm->end_date->endOfDay()));
            if ($todayInTerm) {
                $selectedDate = $today;
            } else {
                $latestRecordedDate = Attendance::query()
                    ->where('term_id', $currentTerm->id)
                    ->whereDate('date', '<=', $today->toDateString())
                    ->when($currentTerm->start_date, fn ($query) => $query->whereDate('date', '>=', $currentTerm->start_date->toDateString()))
                    ->when($currentTerm->end_date, fn ($query) => $query->whereDate('date', '<=', $currentTerm->end_date->toDateString()))
                    ->max('date');
                $selectedDate = $latestRecordedDate ? CarbonImmutable::parse($latestRecordedDate) : null;
            }
        }

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
        $attendance = $currentTerm
            ? $analytics->attendance($currentTerm, $classes, $selectedDate, $selectedClassId)
            : null;
        $enrollment = $attendance['enrollment'] ?? $analytics->enrollment($classes);
        $activeStudents = $analytics->activeStudentCount();
        $activeTeachers = Teacher::whereHas('user', fn ($query) => $query->where('is_active', true))->count();
        $recentAnnouncements = Announcement::query()->latest()->limit(3)->get(['id', 'title', 'is_event', 'event_date', 'created_at']);

        $recentActivity = AuditLog::with('user')
            ->latest()
            ->limit(5)
            ->get();
        $auditPresentation = $auditFormatter->format($recentActivity);

        return view('admin.index', [
            'classes' => $classes,
            'currentTerm' => $currentTerm,
            'selectedClassId' => $selectedClassId,
            'selectedDate' => $selectedDate,
            'activeStudents' => $activeStudents,
            'activeTeachers' => $activeTeachers,
            'totalClasses' => $classes->count(),
            'resultMetrics' => $resultMetrics,
            'finance' => $finance,
            'attendance' => $attendance,
            'enrollment' => $enrollment,
            'recentAnnouncements' => $recentAnnouncements,
            'recentActivity' => $recentActivity,
            'auditPresentation' => $auditPresentation,
        ]);
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
