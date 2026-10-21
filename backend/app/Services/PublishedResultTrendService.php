<?php

namespace App\Services;

use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class PublishedResultTrendService
{
    public function forStudent(Student $student): array
    {
        $terms = DB::table('results')
            ->join('terms', 'results.term_id', '=', 'terms.id')
            ->leftJoin('academic_sessions', 'terms.academic_session_id', '=', 'academic_sessions.id')
            ->where('results.student_id', $student->id)
            ->whereNotNull('results.total')
            ->whereExists(function ($query) use ($student) {
                $query->selectRaw('1')
                    ->from('report_cards')
                    ->whereColumn('report_cards.term_id', 'results.term_id')
                    ->where('report_cards.student_id', $student->id)
                    ->where('report_cards.status', ReportCard::STATUS_PUBLISHED);
            })
            ->select([
                'terms.id as term_id',
                'terms.name as term_name',
                'terms.start_date as term_start_date',
                'academic_sessions.name as session_name',
                'academic_sessions.start_date as session_start_date',
            ])
            ->selectRaw('AVG(results.total) as average_score')
            ->groupBy([
                'terms.id',
                'terms.name',
                'terms.start_date',
                'academic_sessions.name',
                'academic_sessions.start_date',
            ])
            ->orderByRaw('COALESCE(academic_sessions.start_date, terms.start_date) DESC')
            ->orderByDesc('terms.start_date')
            ->orderByDesc('terms.id')
            ->limit(4)
            ->get()
            ->reverse()
            ->values();

        $rows = $terms->map(fn ($term) => [
            'term' => $term->term_name,
            'session' => $term->session_name,
            'average' => round((float) $term->average_score, 1),
            'label' => $term->session_name
                ? "{$term->term_name} · {$term->session_name}"
                : $term->term_name,
        ])->all();

        return [
            'labels' => array_column($rows, 'label'),
            'averages' => array_column($rows, 'average'),
            'rows' => $rows,
        ];
    }
}
