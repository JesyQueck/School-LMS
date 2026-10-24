<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\ParentProfile;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
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

        $totalAnnouncements = Announcement::count();
        $recentAnnouncements = Announcement::latest()->limit(2)->get();

        $finance = $feeService->financeSummary();

        $recentActivity = AuditLog::with('user')
            ->latest()
            ->limit(8)
            ->get();

        $actionLabels = [
            'user.login' => 'User logged in',
            'user.logout' => 'User logged out',
            'parent.password_reset' => 'Parent password reset',
            'account.created' => 'Account created',
            'account.temporary_password_regenerated' => 'Temporary password regenerated',
            'password.reset' => 'Password reset',
            'announcement.created' => 'Announcement created',
            'announcement.updated' => 'Announcement updated',
            'announcement.deleted' => 'Announcement deleted',
            'class.created' => 'Class created',
            'class.updated' => 'Class updated',
            'teacher.created' => 'Teacher created',
            'teacher.updated' => 'Teacher updated',
            'teacher.deleted' => 'Teacher deleted',
            'student.created' => 'Student created',
            'student.updated' => 'Student updated',
            'class_subject.updated' => 'Class subject updated',
            'result.created' => 'Result created',
            'result.locked' => 'Result locked',
            'subject.created' => 'Subject created',
            'subject.updated' => 'Subject updated',
            'subject.deleted' => 'Subject deleted',
            'subject_assignment.created' => 'Subject assignment created',
            'class_assignment.created' => 'Class assignment created',
            'fee_type.created' => 'Fee type created',
            'payment.created' => 'Payment recorded',
            'attendance.marked' => 'Attendance marked',
            'scores.entered' => 'Scores entered',
            'report_card.comment_added' => 'Report card comment added',
            'report_card.submitted' => 'Report card submitted',
            'period_config.saved' => 'Period config saved',
            'timetable.manual_created' => 'Timetable created',
            'timetable.updated' => 'Timetable updated',
            'timetable.deleted' => 'Timetable deleted',
            'timetable.moved' => 'Timetable moved',
            'timetable.swapped' => 'Timetable swapped',
        ];

        return view('admin.index', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalParents',
            'session',
            'termName',
            'activeClasses',
            'subjects',
            'teachersAssigned',
            'totalTeachersForAssignment',
            'resultMetrics',
            'finance',
            'recentActivity',
            'actionLabels',
            'totalAnnouncements',
            'recentAnnouncements'
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
