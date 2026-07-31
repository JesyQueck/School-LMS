<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results</title>
</head>
<body>
    <h1>Results for {{ $student->admission_no }}</h1>

    @forelse($results as $result)
        <div>
            <h3>{{ $result->classSubject->subject->name ?? 'Subject' }}</h3>
            <p>Term: {{ $result->term->name ?? 'Term ' . $result->term_id }}</p>
            <p>Score: {{ $result->score ?? 'N/A' }}</p>
            <p>Grade: {{ $result->grade ?? 'N/A' }}</p>
        </div>
    @empty
        <p>No published results available.</p>
    @endforelse
</body>
</html>
