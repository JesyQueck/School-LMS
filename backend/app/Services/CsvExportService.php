<?php

namespace App\Services;

use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class CsvExportService
{
    public function exportStudents(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $students = Student::with(['user', 'class'])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="students.csv"',
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Full Name', 'Class', 'Gender', 'Status', 'Created At']);

            foreach ($students as $student) {
                fputcsv($file, [
                    $student->admission_no,
                    $student->full_name,
                    $student->class->name ?? 'N/A',
                    $student->gender ?? 'N/A',
                    $student->status ?? 'active',
                    $student->created_at?->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportResults(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $results = Result::with(['student', 'classSubject.subject', 'term'])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="results.csv"',
        ];

        $callback = function () use ($results) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Subject', 'Term', 'CA Score', 'Exam Score', 'Total', 'Grade', 'Is Locked']);

            foreach ($results as $result) {
                fputcsv($file, [
                    $result->student->admission_no ?? 'N/A',
                    $result->student->full_name ?? 'N/A',
                    $result->student->class->name ?? 'N/A',
                    $result->classSubject->subject->name ?? 'N/A',
                    $result->term->name ?? 'N/A',
                    $result->ca_score ?? 'N/A',
                    $result->exam_score ?? 'N/A',
                    $result->total ?? 'N/A',
                    $result->grade ?? 'N/A',
                    $result->is_locked ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
