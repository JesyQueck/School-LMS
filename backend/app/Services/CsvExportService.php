<?php

namespace App\Services;

use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    public function exportStudents(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="students.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Full Name', 'Class', 'Gender', 'Status', 'Created At']);

            Student::with(['user', 'schoolClass'])
                ->orderBy('id')
                ->chunkById(1000, function ($students) use ($file) {
                    foreach ($students as $student) {
                        fputcsv($file, [
                            $student->admission_no,
                            $student->full_name,
                            $student->schoolClass->name ?? 'N/A',
                            $student->gender ?? 'N/A',
                            $student->status ?? 'active',
                            $student->created_at?->format('Y-m-d'),
                        ]);
                    }
                });

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportResults(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="results.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Subject', 'Term', 'CA Score', 'Exam Score', 'Total', 'Grade', 'Is Locked']);

            Result::with(['student', 'classSubject.subject', 'term'])
                ->orderBy('id')
                ->chunkById(1000, function ($results) use ($file) {
                    foreach ($results as $result) {
                        fputcsv($file, [
                            $result->student->admission_no ?? 'N/A',
                            $result->student->full_name ?? 'N/A',
                            $result->student->schoolClass->name ?? 'N/A',
                            $result->classSubject->subject->name ?? 'N/A',
                            $result->term->name ?? 'N/A',
                            $result->ca_score ?? 'N/A',
                            $result->exam_score ?? 'N/A',
                            $result->total ?? 'N/A',
                            $result->grade ?? 'N/A',
                            $result->is_locked ? 'Yes' : 'No',
                        ]);
                    }
                });

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
