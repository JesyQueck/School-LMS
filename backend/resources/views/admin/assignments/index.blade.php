<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Assignments</title>
</head>
<body>
    <h1>Teacher Assignments</h1>

    <form method="POST" action="{{ route('admin.assignments.store') }}">
        @csrf
        <input name="teacher_id" placeholder="Teacher ID" required>
        <input name="class_subject_id" placeholder="Class Subject ID" required>
        <label><input type="checkbox" name="is_active" value="1"> Active</label>
        <button type="submit">Create assignment</button>
    </form>

    <ul>
        @foreach ($assignments as $assignment)
            <li>{{ $assignment->teacher->user->name ?? 'Unknown' }} -> {{ $assignment->classSubject->class->name ?? 'Unknown class' }} / {{ $assignment->classSubject->subject->name ?? 'Unknown subject' }}</li>
        @endforeach
    </ul>
</body>
</html>
