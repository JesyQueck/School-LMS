<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Overview</title>
</head>
<body>
    <h1>{{ $student->admission_no }}</h1>
    <p>Class: {{ $student->class->name ?? 'N/A' }}</p>

    <h2>Published Report Cards</h2>
    @forelse($student->reportCards as $reportCard)
        <p>{{ $reportCard->term->name ?? 'Term ' . $reportCard->term_id }}</p>
    @empty
        <p>No published report cards yet.</p>
    @endforelse
</body>
</html>
