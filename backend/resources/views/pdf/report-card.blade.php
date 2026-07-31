<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Report Card</h1>
    <p><strong>Student:</strong> {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}</p>
    <p><strong>Admission No:</strong> {{ $reportCard->student->admission_no }}</p>
    <p><strong>Class:</strong> {{ $reportCard->student->class->name ?? 'N/A' }}</p>
    <p><strong>Term:</strong> {{ $reportCard->term->name ?? 'N/A' }}</p>
    <p><strong>Published:</strong> {{ $reportCard->is_published ? 'Yes' : 'No' }}</p>

    <h2>Results</h2>
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>CA Score</th>
                <th>Exam Score</th>
                <th>Total</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportCard->student->results as $result)
                <tr>
                    <td>{{ $result->classSubject->subject->name ?? 'N/A' }}</td>
                    <td>{{ $result->ca_score ?? 'N/A' }}</td>
                    <td>{{ $result->exam_score ?? 'N/A' }}</td>
                    <td>{{ $result->total ?? 'N/A' }}</td>
                    <td>{{ $result->grade ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No results available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
