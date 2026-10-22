<?php

namespace App\Console\Commands;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class SeedSyntheticAttendanceDemo extends Command
{
    protected $signature = 'attendance:seed-synthetic-demo
        {--from=2026-09-01 : First date to consider (inclusive)}
        {--through=2026-10-22 : Last date to consider (inclusive)}
        {--seed=20261022 : Deterministic distribution seed}
        {--present=90 : Percentage of present outcomes}
        {--absent=6 : Percentage of absent outcomes}
        {--late=4 : Percentage of late outcomes}
        {--marked-by= : Active administrator user ID required for insertion}
        {--assume-current-roster : Acknowledge that historical class transfers are not tracked}
        {--confirm-synthetic : Insert missing synthetic records; preview is the default}';

    protected $description = 'Preview or explicitly create synthetic attendance demo records';

    public function handle(): int
    {
        try {
            $from = $this->parseDate((string) $this->option('from'));
            $through = $this->parseDate((string) $this->option('through'));
            $seed = filter_var($this->option('seed'), FILTER_VALIDATE_INT);
            $distribution = $this->distribution();
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($through->lessThan($from)) {
            $this->components->error('The through date must be on or after the from date.');

            return self::FAILURE;
        }

        if ($seed === false) {
            $this->components->error('The seed must be an integer.');

            return self::FAILURE;
        }

        $dates = $this->weekdays($from, $through);
        if ($dates->isEmpty()) {
            $this->components->error('The requested period contains no weekdays.');

            return self::FAILURE;
        }

        $sessions = AcademicSession::query()->where('is_current', true)->get();
        if ($sessions->count() !== 1) {
            $this->components->error('Expected exactly one configured current academic session; no records were written.');

            return self::FAILURE;
        }

        $session = $sessions->first();
        foreach ($dates as $date) {
            if (! $session->start_date || ! $session->end_date
                || $date->lessThan($session->start_date)
                || $date->greaterThan($session->end_date)) {
                $this->components->error(sprintf(
                    'Date %s is outside the configured current academic session (%s to %s); no records were written.',
                    $date->toDateString(),
                    $session->start_date?->toDateString() ?? 'unset',
                    $session->end_date?->toDateString() ?? 'unset'
                ));

                return self::FAILURE;
            }
        }

        $terms = Term::query()
            ->where('academic_session_id', $session->id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();
        $termByDate = [];

        foreach ($dates as $date) {
            $coveringTerms = $terms->filter(
                fn (Term $term) => $date->greaterThanOrEqualTo($term->start_date)
                    && $date->lessThanOrEqualTo($term->end_date)
            );

            if ($coveringTerms->count() !== 1) {
                $this->components->error(sprintf(
                    'Date %s maps to %d configured terms in the current session; every date must map to exactly one term. No records were written.',
                    $date->toDateString(),
                    $coveringTerms->count()
                ));

                return self::FAILURE;
            }

            $termByDate[$date->toDateString()] = $coveringTerms->first();
        }

        $this->line('Term mapping:');
        foreach (collect($termByDate)->groupBy(fn (Term $term) => $term->id) as $termDates) {
            $term = $termDates->first();
            $currentLabel = $term->is_current ? ' (configured current term)' : '';
            $this->line(sprintf('  %s%s: %d weekdays.', $term->name, $currentLabel, $termDates->count()));
        }

        $students = Student::query()
            ->where('status', 'active')
            ->whereNotNull('class_id')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->get(['id', 'class_id', 'admission_date']);

        $candidates = [];
        $eligibleStudentIds = [];
        $missingAdmissionDateIds = [];
        $recordsByClass = [];

        foreach ($dates as $date) {
            $term = $termByDate[$date->toDateString()];

            foreach ($students as $student) {
                if ($student->admission_date && $student->admission_date->greaterThan($date)) {
                    continue;
                }

                $eligibleStudentIds[$student->id] = true;
                if (! $student->admission_date) {
                    $missingAdmissionDateIds[$student->id] = true;
                }
                $recordsByClass[$student->class_id] = ($recordsByClass[$student->class_id] ?? 0) + 1;

                $candidates[] = [
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'term_id' => $term->id,
                    'date' => $date->toDateString(),
                    'status' => $this->statusFor($seed, $student->id, $date->toDateString(), $term->id, $distribution),
                ];
            }
        }

        $existingRows = Attendance::query()
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $through->toDateString())
            ->get(['student_id', 'class_id', 'term_id', 'date']);
        $existingByKey = $existingRows->keyBy(
            fn (Attendance $attendance) => $this->recordKey(
                $attendance->student_id,
                $attendance->date->toDateString(),
                $attendance->term_id
            )
        );

        $proposed = [];
        $skipped = 0;
        $conflicts = [];

        foreach ($candidates as $candidate) {
            $key = $this->recordKey($candidate['student_id'], $candidate['date'], $candidate['term_id']);
            $existing = $existingByKey->get($key);

            if (! $existing) {
                $proposed[] = $candidate;

                continue;
            }

            $skipped++;
            if ((int) $existing->class_id !== (int) $candidate['class_id']) {
                $conflicts[] = $candidate;
            }
        }

        $this->components->info('SYNTHETIC ATTENDANCE DEMO — preview only unless --confirm-synthetic is supplied.');
        $this->line('Warning: these simulated outcomes are not genuine historical attendance and must not be treated as real or left in production.');
        $this->line('Roster note: the app stores only each student’s current class_id, not dated class-transfer history.');
        $this->line(sprintf('Session: %s (%s to %s)', $session->name, $session->start_date->toDateString(), $session->end_date->toDateString()));
        $this->line(sprintf('Period: %s through %s inclusive; %d weekdays; no holiday/calendar table is configured.', $from->toDateString(), $through->toDateString(), $dates->count()));
        $this->line(sprintf('Active eligible students in at least one date roster: %d; missing admission_date: %d.', count($eligibleStudentIds), count($missingAdmissionDateIds)));
        $this->line(sprintf('Expected attendance records: %d.', count($candidates)));
        if ($recordsByClass !== []) {
            $classNames = DB::table('classes')->whereIn('id', array_keys($recordsByClass))->pluck('name', 'id');
            $this->line('Expected records by current class roster:');
            foreach ($recordsByClass as $classId => $count) {
                $this->line(sprintf('  %s: %d.', $classNames->get($classId, "Class {$classId}"), $count));
            }
        }
        $this->line(sprintf('Existing attendance records in date window (all terms): %d.', $existingRows->count()));
        $this->line(sprintf('Existing same-key records preserved/skipped: %d; class mismatches requiring review: %d.', $skipped, count($conflicts)));
        $this->line(sprintf('Proposed inserts: %d; other existing records will remain untouched.', count($proposed)));
        $this->line(sprintf(
            'Outcome mix: present %d%%, absent %d%%, late %d%%; seed %d.',
            $distribution['present'],
            $distribution['absent'],
            $distribution['late'],
            $seed
        ));
        $this->line('Synthetic provenance column available: '.(Schema::hasColumn('attendances', 'is_synthetic') ? 'yes' : 'no; run the additive migration before insertion.'));

        if ($conflicts !== []) {
            $this->components->error('A pre-existing attendance key points to a different class than the current roster. Resolve the class-history conflict before insertion.');

            return self::FAILURE;
        }

        if (! $this->option('confirm-synthetic')) {
            $this->line('Dry run complete. No attendance records were written.');

            return self::SUCCESS;
        }

        if (app()->environment('production')) {
            $this->components->error('Synthetic attendance insertion is disabled in production.');

            return self::FAILURE;
        }

        if (! $this->option('assume-current-roster')) {
            $this->components->error('Insertion requires --assume-current-roster because historical class membership is not tracked.');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('attendances', 'is_synthetic')) {
            $this->components->error('Run the additive is_synthetic migration before insertion. No records were written.');

            return self::FAILURE;
        }

        $markedBy = filter_var($this->option('marked-by'), FILTER_VALIDATE_INT);
        if (! $markedBy || ! User::query()
            ->whereKey($markedBy)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->exists()) {
            $this->components->error('--marked-by must identify an active administrator user. No records were written.');

            return self::FAILURE;
        }

        $now = now();
        $rows = array_map(fn (array $candidate) => [
            ...$candidate,
            'marked_by' => $markedBy,
            'is_synthetic' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $proposed);

        $inserted = DB::transaction(function () use ($rows) {
            $inserted = 0;
            foreach (array_chunk($rows, 500) as $chunk) {
                $inserted += DB::table('attendances')->insertOrIgnore($chunk);
            }

            return $inserted;
        });

        $this->components->info(sprintf(
            'Synthetic insertion complete: %d inserted, %d skipped because a matching record appeared concurrently, %d pre-existing same-key records preserved.',
            $inserted,
            count($proposed) - $inserted,
            $skipped
        ));

        return self::SUCCESS;
    }

    private function parseDate(string $value): CarbonImmutable
    {
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        if (! $date || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException(sprintf('Invalid date "%s"; use YYYY-MM-DD.', $value));
        }

        return $date;
    }

    private function distribution(): array
    {
        $values = [];
        foreach (['present', 'absent', 'late'] as $status) {
            $value = filter_var($this->option($status), FILTER_VALIDATE_INT);
            if ($value === false || $value < 0 || $value > 100) {
                throw new InvalidArgumentException(sprintf('The %s percentage must be an integer from 0 to 100.', $status));
            }
            $values[$status] = $value;
        }

        if (array_sum($values) !== 100) {
            throw new InvalidArgumentException('Present, absent, and late percentages must sum to 100.');
        }

        return $values;
    }

    private function weekdays(CarbonImmutable $from, CarbonImmutable $through): Collection
    {
        $dates = collect();
        for ($date = $from; $date->lessThanOrEqualTo($through); $date = $date->addDay()) {
            if ($date->isWeekday()) {
                $dates->push($date);
            }
        }

        return $dates;
    }

    private function statusFor(int $seed, int $studentId, string $date, int $termId, array $distribution): string
    {
        $bucket = hexdec(substr(hash('sha256', "{$seed}|{$studentId}|{$date}|{$termId}"), 0, 7)) % 100;
        if ($bucket < $distribution['present']) {
            return 'present';
        }
        if ($bucket < $distribution['present'] + $distribution['absent']) {
            return 'absent';
        }

        return 'late';
    }

    private function recordKey(int|string $studentId, string $date, int|string $termId): string
    {
        return "{$studentId}|{$date}|{$termId}";
    }
}
