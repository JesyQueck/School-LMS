<?php

namespace App\Services;

use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportCardService
{
    /**
     * Generate a report card for a student/term.
     */
    public function generateReportCard(array $data): ReportCard
    {
        return ReportCard::create([
            'student_id' => $data['student_id'],
            'term_id' => $data['term_id'],
            'class_teacher_remark' => $data['class_teacher_remark'] ?? null,
            'principal_remark' => $data['principal_remark'] ?? null,
            'position_in_class' => $data['position_in_class'] ?? null,
            'total_students_in_class' => $data['total_students_in_class'] ?? null,
            'next_term_begins' => $data['next_term_begins'] ?? null,
            'is_published' => false,
            'generated_at' => now(),
        ]);
    }

    /**
     * Publish a report card.
     *
     * Business rule (ARCHITECTURE §8): a report card cannot be published
     * unless the relevant term is locked. Publishing records who published
     * and when, and is wrapped in a transaction.
     */
    public function publish(ReportCard $reportCard, User $publishedBy): ReportCard
    {
        $term = $reportCard->term;

        if ($term && ! $this->termResultsAreLocked($term)) {
            throw new \RuntimeException(
                'Report cards cannot be published until the term results are locked.'
            );
        }

        return DB::transaction(function () use ($reportCard, $publishedBy) {
            $reportCard->update([
                'is_published' => true,
                'published_by' => $publishedBy->id,
                'published_at' => now(),
            ]);

            return $reportCard->refresh();
        });
    }

    /**
     * Unpublish a report card so corrections can be made (admin only).
     */
    public function unpublish(ReportCard $reportCard): ReportCard
    {
        return DB::transaction(function () use ($reportCard) {
            $reportCard->update([
                'is_published' => false,
                'published_by' => null,
                'published_at' => null,
            ]);

            return $reportCard->refresh();
        });
    }

    /**
     * Determine whether all results for a term are locked.
     * A term is considered "locked" when every result row for that term
     * has is_locked = true (or there are no results yet).
     */
    public function termResultsAreLocked(Term $term): bool
    {
        $unlocked = $term->results()->where('is_locked', false)->count();

        return $unlocked === 0;
    }
}
