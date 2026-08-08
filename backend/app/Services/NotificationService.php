<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentProfile;
use Carbon\Carbon;

class NotificationService
{
    public function sendToUser(User $user, string $title, string $message, string $type = 'info', array $data = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'recipient_role' => $user->role,
            'title' => $title,
            'message' => $message,
            'data' => json_encode($data),
            'type' => $type,
            'is_read' => false,
        ]);
    }

    public function sendToRole(string $role, string $title, string $message, string $type = 'info', array $data = []): void
    {
        User::where('role', $role)->chunk(100, function ($users) use ($title, $message, $type, $data) {
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'recipient_role' => $role,
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode($data),
                    'type' => $type,
                    'is_read' => false,
                ]);
            }
        });
    }

    public function notifyScoreDeadlineApproaching(Student $student, string $subjectName): void
    {
        $teacher = $student->classSubjectTeachers($subjectName);
        if ($teacher) {
            $user = $teacher->user;
            $this->sendToUser($user, 'Score Submission Deadline Approaching', 
                "Submit CA/Exam scores for {$student->full_name} ({$subjectName}) before the deadline.", 
                'warning', 
                ['student_id' => $student->id, 'subject' => $subjectName]);
        }
    }

    public function notifyClassTeacherAllScoresReceived($classAssignment): void
    {
        $teacher = $classAssignment->teacher;
        if ($teacher && $teacher->user) {
            $this->sendToUser($teacher->user, 'All Subject Scores Received', 
                "All subject scores have been received for {$classAssignment->class->name}. Ready to generate report cards.", 
                'success', 
                ['class_id' => $classAssignment->class_id, 'term_id' => $classAssignment->term_id]);
        }
    }

    public function notifyPrincipalPendingApproval(int $count): void
    {
        $this->sendToRole('admin', 'Report Cards Awaiting Approval', 
            "{$count} report cards are awaiting your approval.", 
            'info', 
            ['pending_count' => $count]);
    }

    public function notifyParentReportCardPublished(Student $student): void
    {
        $parent = ParentProfile::where('student_id', $student->id)->first();
        if ($parent && $parent->user) {
            $this->sendToUser($parent->user, 'Report Card Published', 
                "Report card for {$student->full_name} has been published and is now available to view.", 
                'success', 
                ['student_id' => $student->id]);
        }
    }

    public function notifyStudentResultsAvailable(Student $student): void
    {
        $user = $student->user;
        if ($user) {
            $this->sendToUser($user, 'Results Available', 
                "Your results for the current term are now available. Check your report card.", 
                'success', 
                ['student_id' => $student->id]);
        }
    }
}
