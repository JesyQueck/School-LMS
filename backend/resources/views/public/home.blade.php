<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our School</title>
</head>
<body>
    <h1>Welcome to Our School</h1>
    <p>Providing quality education with excellence in academics and character.</p>

    <h2>Latest Announcements</h2>
    @forelse($announcements as $announcement)
        <div>
            <h3>{{ $announcement->title }}</h3>
            <p>{{ $announcement->body }}</p>
        </div>
    @empty
        <p>No announcements at this time.</p>
    @endforelse

    <p><a href="{{ route('about') }}">About Us</a> | <a href="{{ route('admissions') }}">Admissions</a> | <a href="{{ route('contact') }}">Contact</a></p>
</body>
</html>
