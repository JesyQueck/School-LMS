<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentFee;
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

    public function exportDailyCollection($dailyData, $term): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="daily-collection-'.$term->name.'-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($dailyData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Receipt No', 'Student', 'Admission No', 'Class', 'Fee Type', 'Amount', 'Method', 'Reference', 'Recorded By']);

            foreach ($dailyData as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['receipt_number'],
                    $row['student_name'],
                    $row['admission_no'],
                    $row['class'],
                    $row['fee_type'],
                    $row['amount_paid'],
                    $row['payment_method'],
                    $row['reference'],
                    $row['recorded_by'],
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportOutstandingDebtors($debtors): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="outstanding-debtors-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($debtors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Student', 'Admission No', 'Class', 'Fee Type', 'Term', 'Expected', 'Paid', 'Outstanding', 'Status']);

            foreach ($debtors as $debtor) {
                fputcsv($file, [
                    $debtor['student_name'],
                    $debtor['admission_no'],
                    $debtor['class'],
                    $debtor['fee_type'],
                    $debtor['term'],
                    $debtor['expected'],
                    $debtor['paid'],
                    $debtor['outstanding'],
                    $debtor['status'],
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportStudentStatement($statement, $student): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="statement-'.$student->admission_no.'-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($statement, $student) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Student', 'Admission No', 'Class', 'Fee Type', 'Term', 'Expected', 'Paid', 'Outstanding', 'Status', 'Payment Date', 'Receipt No', 'Payment Amount', 'Payment Method', 'Reference', 'Recorded By']);

            foreach ($statement as $fee) {
                if ($fee['payments']->count() > 0) {
                    foreach ($fee['payments'] as $payment) {
                        fputcsv($file, [
                            $student->full_name,
                            $student->admission_no,
                            $student->schoolClass->name ?? 'N/A',
                            $fee['fee_type'],
                            $fee['term'],
                            $fee['expected'],
                            $fee['paid'],
                            $fee['outstanding'],
                            $fee['status'],
                            $payment['date'],
                            $payment['receipt_number'],
                            $payment['amount'],
                            $payment['method'],
                            $payment['reference'],
                            $payment['recorded_by'],
                        ]);
                    }
                } else {
                    fputcsv($file, [
                        $student->full_name,
                        $student->admission_no,
                        $student->schoolClass->name ?? 'N/A',
                        $fee['fee_type'],
                        $fee['term'],
                        $fee['expected'],
                        $fee['paid'],
                        $fee['outstanding'],
                        $fee['status'],
                        '', '', '', '', '', '',
                    ]);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
