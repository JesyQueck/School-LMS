<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Map technical action keys to plain-language labels.
     */
    protected function actionLabels(): array
    {
        return [
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
    }

    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('target_model')) {
            $query->where('target_model', $request->target_model);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'actionLabels' => $this->actionLabels(),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', [
            'auditLog' => $auditLog,
            'actionLabels' => $this->actionLabels(),
        ]);
    }
}