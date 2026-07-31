<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
</head>
<body>
    <h1>Announcements</h1>

    @forelse($announcements as $announcement)
        <div>
            <h3>{{ $announcement->title }}</h3>
            <p>{{ $announcement->body }}</p>
            <small>By {{ $announcement->createdBy->name ?? 'Admin' }}</small>
        </div>
    @empty
        <p>No announcements.</p>
    @endforelse

    @if($announcements->hasPages())
        <div>
            {{ $announcements->links() }}
        </div>
    @endif
</body>
</html>
