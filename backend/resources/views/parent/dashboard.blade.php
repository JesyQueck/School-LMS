<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
</head>
<body>
    <h1>Parent Dashboard</h1>
    <h2>My Children</h2>
    @forelse($children as $child)
        <p>
            <a href="{{ route('parent.children.show', $child) }}">
                {{ $child->user->name ?? $child->admission_no }} ({{ $child->admission_no }})
            </a>
        </p>
    @empty
        <p>No children linked to your account.</p>
    @endforelse

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
