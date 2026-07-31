<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
</head>
<body>
    <h1>Student Dashboard</h1>
    <h2>Welcome, {{ $student->full_name ?? $student->admission_no }}</h2>
    <p>Admission No: {{ $student->admission_no }}</p>
    <p>Class: {{ $student->class->name ?? 'N/A' }}</p>

    <h2>Announcements</h2>
    @forelse($announcements as $announcement)
        <div>
            <h3>{{ $announcement->title }}</h3>
            <p>{{ $announcement->body }}</p>
        </div>
    @empty
        <p>No announcements.</p>
    @endforelse
</body>
</html>
