<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Report Cards</title>
</head>
<body>
    <h1>Report Cards for {{ $student->full_name ?? $student->admission_no }}</h1>
    <p>Admission No: {{ $student->admission_no }}</p>

    @forelse($reportCards as $reportCard)
        <div>
            <h3>{{ $reportCard->term->name ?? 'Term ' . $reportCard->term_id }}</h3>
            <p>Published: {{ $reportCard->is_published ? 'Yes' : 'No' }}</p>
        </div>
    @empty
        <p>No published report cards yet.</p>
    @endforelse
</body>
</html>
