<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers</title>
</head>
<body>
    <h1>Teachers</h1>
    <form method="POST" action="{{ route('admin.teachers.store') }}">
        @csrf
        <input name="user_id" placeholder="User ID" required>
        <input name="specialization" placeholder="Specialization">
        <button type="submit">Create teacher</button>
    </form>
    <ul>
        @foreach ($teachers as $teacher)
            <li>{{ $teacher->user->name ?? 'Unknown' }} - {{ $teacher->specialization ?? 'N/A' }}</li>
        @endforeach
    </ul>
</body>
</html>
