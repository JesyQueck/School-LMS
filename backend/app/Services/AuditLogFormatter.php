<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\ClassAssignment;
use App\Models\ClassSubject;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\PeriodConfig;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AuditLogFormatter
{
    private const ACTION_LABELS = [
        'account.created' => 'Account created',
        'account.temporary_password_regenerated' => 'Temporary password regenerated',
        'announcement.created' => 'Announcement created',
        'announcement.deleted' => 'Announcement deleted',
        'announcement.updated' => 'Announcement updated',
        'attendance.marked' => 'Attendance marked',
        'class.created' => 'Class created',
        'class.updated' => 'Class updated',
        'class_assignment.created' => 'Class assignment created',
        'class_subject.updated' => 'Class subject updated',
        'fee_type.created' => 'Fee type created',
        'parent.password_reset' => 'Parent password reset',
        'password.reset' => 'Password reset',
        'payment.created' => 'Payment recorded',
        'period_config.saved' => 'Period configuration saved',
        'report_card.approved' => 'Report card approved',
        'report_card.comment_added' => 'Report card comment added',
        'report_card.published' => 'Report card published',
        'report_card.returned' => 'Report card returned',
        'report_card.submitted' => 'Report card submitted',
        'report_card.unpublished' => 'Report card unpublished',
        'report_card.updated' => 'Report card updated',
        'report_cards.published_all' => 'Report cards published',
        'result.created' => 'Result recorded',
        'result.locked' => 'Result locked',
        'scores.entered' => 'Scores entered',
        'student.created' => 'Student created',
        'student.updated' => 'Student updated',
        'subject.created' => 'Subject created',
        'subject.deleted' => 'Subject deleted',
        'subject.updated' => 'Subject updated',
        'subject_assignment.created' => 'Subject assignment created',
        'teacher.created' => 'Teacher created',
        'teacher.deleted' => 'Teacher deleted',
        'teacher.updated' => 'Teacher updated',
        'timetable.deleted' => 'Timetable entry deleted',
        'timetable.manual_created' => 'Timetable entry created',
        'timetable.moved' => 'Timetable entry moved',
        'timetable.swapped' => 'Timetable entries swapped',
        'timetable.updated' => 'Timetable entry updated',
        'user.login' => 'Logged in',
        'user.logout' => 'Logged out',
    ];

    /**
     * @param  iterable<AuditLog>  $logs
     * @return array<int, array{action: string, target: string, actor: string}>
     */
    public function format(iterable $logs): array
    {
        $logs = collect($logs);
        $targets = $this->loadTargets($logs);

        return $logs->mapWithKeys(function (AuditLog $log) use ($targets): array {
            $target = $targets[$log->target_model][$log->target_id] ?? null;

            return [$log->id => [
                'action' => self::ACTION_LABELS[$log->action] ?? Str::headline(str_replace(['.', '_'], ' ', $log->action)),
                'target' => $this->targetLabel($log, $target),
                'actor' => $log->user?->name ?? 'Unknown user',
            ]];
        })->all();
    }

    private function loadTargets(Collection $logs): array
    {
        $targetIds = $logs
            ->filter(fn (AuditLog $log) => $log->target_model && $log->target_id)
            ->groupBy('target_model')
            ->map(fn (Collection $group) => $group->pluck('target_id')->unique()->values());
        $targets = [];

        foreach ($targetIds as $modelClass => $ids) {
            $query = match ($modelClass) {
                User::class => User::query()->whereIn('id', $ids),
                Student::class => Student::query()->with('user')->whereIn('id', $ids),
                Teacher::class => Teacher::query()->with('user')->whereIn('id', $ids),
                SchoolClass::class => SchoolClass::query()->whereIn('id', $ids),
                Subject::class => Subject::query()->whereIn('id', $ids),
                ClassSubject::class => ClassSubject::query()->with(['class', 'subject'])->whereIn('id', $ids),
                ClassAssignment::class => ClassAssignment::query()->with(['class', 'teacher.user'])->whereIn('id', $ids),
                TeacherClassSubject::class => TeacherClassSubject::query()->with(['teacher.user', 'classSubject.class', 'classSubject.subject'])->whereIn('id', $ids),
                FeeType::class => FeeType::query()->with(['class', 'term'])->whereIn('id', $ids),
                Payment::class => Payment::query()->with('studentFee.student.user')->whereIn('id', $ids),
                Result::class => Result::query()->with(['student.user', 'classSubject.subject', 'term'])->whereIn('id', $ids),
                ReportCard::class => ReportCard::query()->with(['student.user', 'term'])->whereIn('id', $ids),
                Announcement::class => Announcement::query()->whereIn('id', $ids),
                Attendance::class => Attendance::query()->with(['class', 'student.user', 'term'])->whereIn('id', $ids),
                Timetable::class => Timetable::query()->with(['classSubject.class', 'classSubject.subject'])->whereIn('id', $ids),
                PeriodConfig::class => PeriodConfig::query()->with('term')->whereIn('id', $ids),
                default => null,
            };

            if ($query) {
                $targets[$modelClass] = $query->get()->keyBy('id')->all();
            }
        }

        return $targets;
    }

    private function targetLabel(AuditLog $log, ?object $target): string
    {
        if (! $log->target_model) {
            return 'Not specified';
        }

        if ($log->target_id && ! $target && $this->isSupportedTarget($log->target_model)) {
            return 'Record no longer available';
        }

        if ($log->target_id && ! $this->isSupportedTarget($log->target_model)) {
            return 'Target record #'.$log->target_id;
        }

        if (! $log->target_id && $log->target_model === Attendance::class) {
            return 'Attendance register';
        }

        if (! $log->target_id && $log->target_model === 'subject_results') {
            return 'Subject results';
        }

        if ($target instanceof User) {
            if (in_array($log->action, ['user.login', 'user.logout'], true)) {
                return Str::headline($target->role ?: 'user').' account';
            }

            return $target->name.' · '.Str::headline($target->role ?: 'user');
        }

        if ($target instanceof Student) {
            return $this->withIdentifier($target->full_name, $target->admission_no);
        }

        if ($target instanceof Teacher) {
            return $target->user?->name ?: ($target->employee_id ?: 'Teacher');
        }

        if ($target instanceof SchoolClass || $target instanceof Subject || $target instanceof Announcement) {
            return $target->name ?? $target->title;
        }

        if ($target instanceof ClassSubject) {
            return $this->joinLabels($target->class?->name, $target->subject?->name);
        }

        if ($target instanceof ClassAssignment) {
            return $this->joinLabels($target->teacher?->user?->name, $target->class?->name);
        }

        if ($target instanceof TeacherClassSubject) {
            return $this->joinLabels(
                $target->teacher?->user?->name,
                $target->classSubject?->subject?->name,
                $target->classSubject?->class?->name
            );
        }

        if ($target instanceof FeeType) {
            return $this->joinLabels($target->name, $target->term?->name);
        }

        if ($target instanceof Payment) {
            return $this->joinLabels(
                $target->receipt_number ? 'Payment '.$target->receipt_number : 'Payment',
                $target->studentFee?->student?->full_name
            );
        }

        if ($target instanceof Result) {
            return $this->joinLabels(
                $target->student?->full_name,
                $target->classSubject?->subject?->name,
                $target->term?->name
            );
        }

        if ($target instanceof ReportCard) {
            return $this->joinLabels($target->student?->full_name, $target->term?->name);
        }

        if ($target instanceof Attendance) {
            return $this->joinLabels(
                $target->class?->name,
                $target->date?->format('j M Y'),
                'Attendance register'
            );
        }

        if ($target instanceof Timetable) {
            return $this->joinLabels($target->classSubject?->class?->name, $target->classSubject?->subject?->name);
        }

        if ($target instanceof PeriodConfig) {
            return $target->term?->name ?: 'Period configuration';
        }

        return $this->modelLabel($log->target_model).($log->target_id ? ' #'.$log->target_id : '');
    }

    private function isSupportedTarget(string $modelClass): bool
    {
        return in_array($modelClass, [
            User::class,
            Student::class,
            Teacher::class,
            SchoolClass::class,
            Subject::class,
            ClassSubject::class,
            ClassAssignment::class,
            TeacherClassSubject::class,
            FeeType::class,
            Payment::class,
            Result::class,
            ReportCard::class,
            Announcement::class,
            Attendance::class,
            Timetable::class,
            PeriodConfig::class,
        ], true);
    }

    private function modelLabel(string $modelClass): string
    {
        return Str::headline(class_basename($modelClass));
    }

    private function withIdentifier(string $label, ?string $identifier): string
    {
        return $identifier ? $label.' · '.$identifier : $label;
    }

    private function joinLabels(?string ...$labels): string
    {
        return collect($labels)->filter()->implode(' · ') ?: 'Not specified';
    }
}
