<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content=device-width, initial-scale=1.0">
    <title>My Attendance</title>
</head>
<body>
    <h1>Attendance for {{ $student->full_name ?? $student->admission_no }}</h1>
    <p>Admission No: {{ $student->admission_no }}</p>

    @forelse($attendance as $record)
        <div>
            <p>Date: {{ $record->date }}</p>
            <p>Term: {{ $record->term->name ?? 'N/A' }}</p>
            <p>Status: {{ $record->status ?? 'N/A' }}</p>
        </div>
    @empty
        <p>No attendance records found.</p>
    @endforelse
</body>
</html>
