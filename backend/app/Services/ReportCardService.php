<?php

namespace App\Services;

use App\Models\ReportCard;
use App\Models\Result;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReportCardService
{
    public function generateReportCard(array $data): ReportCard
    {
        $student = Student::findOrFail($data['student_id']);

        return ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $data['term_id'],
            'class_id' => $student->class_id,
            'class_teacher_remark' => $data['class_teacher_remark'] ?? null,
            'principal_remark' => $data['principal_remark'] ?? null,
            'affective_domain' => $data['affective_domain'] ?? null,
            'psychomotor_assessment' => $data['psychomotor_assessment'] ?? null,
            'health_remarks' => $data['health_remarks'] ?? null,
            'position_in_class' => $data['position_in_class'] ?? null,
            'total_students_in_class' => $data['total_students_in_class'] ?? null,
            'promotion_decision' => $data['promotion_decision'] ?? null,
            'next_term_begins' => $data['next_term_begins'] ?? null,
            'is_published' => false,
            'status' => ReportCard::STATUS_DRAFT,
            'generated_at' => now(),
        ]);
    }

    public function saveReportCard(array $data, ?ReportCard $existing = null): ReportCard
    {
        $student = Student::findOrFail($data['student_id']);

        $payload = [
            'student_id' => $student->id,
            'term_id' => $data['term_id'],
            'class_id' => $student->class_id,
            'class_teacher_remark' => $data['class_teacher_remark'] ?? null,
            'principal_remark' => $data['principal_remark'] ?? null,
            'affective_domain' => $data['affective_domain'] ?? null,
            'psychomotor_assessment' => $data['psychomotor_assessment'] ?? null,
            'health_remarks' => $data['health_remarks'] ?? null,
            'position_in_class' => $data['position_in_class'] ?? null,
            'total_students_in_class' => $data['total_students_in_class'] ?? null,
        ];

        if ($existing) {
            if (! $existing->canEdit()) {
                throw new RuntimeException(
                    'This report card has been published and cannot be edited. Unpublish it first if corrections are required.'
                );
            }
            $existing->update($payload);

            return $existing->fresh();
        }

        return ReportCard::create(array_merge($payload, [
            'is_published' => false,
            'status' => ReportCard::STATUS_DRAFT,
            'generated_at' => now(),
        ]));
    }

    public function returnForCorrection(ReportCard $reportCard): ReportCard
    {
        if ($reportCard->is_published) {
            throw new RuntimeException('Published report cards cannot be returned for correction.');
        }

        if (! $reportCard->canTransitionTo(ReportCard::STATUS_RETURNED)) {
            throw new RuntimeException(
                "Report card in '{$reportCard->status}' status cannot be returned for correction."
            );
        }

        return DB::transaction(function () use ($reportCard) {
            $reportCard->update([
                'status' => ReportCard::STATUS_RETURNED,
                'is_published' => false,
            ]);

            return $reportCard->fresh();
        });
    }

    public function approve(ReportCard $reportCard): ReportCard
    {
        if ($reportCard->is_published) {
            throw new RuntimeException('Published report cards cannot be approved.');
        }

        if (! $reportCard->canTransitionTo(ReportCard::STATUS_APPROVED)) {
            throw new RuntimeException(
                "Report card in '{$reportCard->status}' status cannot be approved."
            );
        }

        return DB::transaction(function () use ($reportCard) {
            $reportCard->update(['status' => ReportCard::STATUS_APPROVED]);

            return $reportCard->fresh();
        });
    }

    public function submitForApproval(ReportCard $reportCard, User $submittedBy): ReportCard
    {
        return $this->approve($reportCard);
    }

    public function publish(ReportCard $reportCard, User $publishedBy): ReportCard
    {
        if ($reportCard->is_published) {
            throw new RuntimeException('Report card is already published.');
        }

        $this->validatePublication($reportCard);

        return DB::transaction(function () use ($reportCard, $publishedBy) {
            $reportCard->update([
                'is_published' => true,
                'status' => ReportCard::STATUS_PUBLISHED,
                'published_by' => $publishedBy->id,
                'published_at' => now(),
            ]);

            $this->lockRelatedResults($reportCard);

            $reportCard->refresh();

            return $reportCard;
        });
    }

    public function publishAll(int $termId, User $publishedBy): array
    {
        $term = Term::findOrFail($termId);

        if (! $this->termResultsAreLocked($term)) {
            throw new RuntimeException(
                'Report cards cannot be published until the term results are locked.'
            );
        }

        $candidateCards = ReportCard::where('term_id', $termId)
            ->where('status', ReportCard::STATUS_APPROVED)
            ->where('is_published', false)
            ->get();

        $published = 0;
        $skipped = 0;
        $skippedReasons = [];

        DB::transaction(function () use ($candidateCards, $publishedBy, &$published, &$skipped, &$skippedReasons) {
            foreach ($candidateCards as $card) {
                try {
                    $this->publish($card, $publishedBy);
                    $published++;
                } catch (RuntimeException $e) {
                    $skipped++;
                    $skippedReasons[] = [
                        'report_card_id' => $card->id,
                        'reason' => $e->getMessage(),
                    ];
                }
            }
        });

        return [
            'published' => $published,
            'skipped' => $skipped,
            'skipped_reasons' => $skippedReasons,
        ];
    }

    public function unpublish(ReportCard $reportCard): ReportCard
    {
        if (! $reportCard->is_published) {
            throw new RuntimeException('Report card is not published.');
        }

        return DB::transaction(function () use ($reportCard) {
            $reportCard->update([
                'is_published' => false,
                'status' => ReportCard::STATUS_DRAFT,
                'published_by' => null,
                'published_at' => null,
            ]);

            $this->unlockRelatedResults($reportCard);

            return $reportCard->refresh();
        });
    }

    public function termResultsAreLocked(?Term $term): bool
    {
        if (! $term) {
            return false;
        }

        return ! $term->results()
            ->where('is_locked', false)
            ->exists();
    }

    public function hasRequiredResults(ReportCard $reportCard): bool
    {
        if (! $reportCard->student) {
            return false;
        }

        return $reportCard->student
            ->results()
            ->where('term_id', $reportCard->term_id)
            ->whereNotNull('ca_score')
            ->whereNotNull('exam_score')
            ->exists();
    }

    public function validatePublication(ReportCard $reportCard): void
    {
        if (! $reportCard->student) {
            throw new RuntimeException('Report card must belong to a valid student.');
        }

        if (! $reportCard->term) {
            throw new RuntimeException('Report card must belong to a valid term.');
        }

        if (! $reportCard->class_id) {
            throw new RuntimeException('Report card must have a valid class.');
        }

        $term = $reportCard->term;

        if (! $this->termResultsAreLocked($term)) {
            throw new RuntimeException(
                'Report cards cannot be published until the term results are locked.'
            );
        }

        if (! $this->hasRequiredResults($reportCard)) {
            throw new RuntimeException(
                'Report card cannot be published: no complete result records found for this student.'
            );
        }
    }

    protected function lockRelatedResults(ReportCard $reportCard): void
    {
        Result::where('student_id', $reportCard->student_id)
            ->where('term_id', $reportCard->term_id)
            ->where('is_locked', false)
            ->update(['is_locked' => true]);
    }

    protected function unlockRelatedResults(ReportCard $reportCard): void
    {
        Result::where('student_id', $reportCard->student_id)
            ->where('term_id', $reportCard->term_id)
            ->where('is_locked', true)
            ->update(['is_locked' => false]);
    }
}
