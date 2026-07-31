<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
</head>
<body>
    <h1>Students</h1>
    <form method="POST" action="{{ route('admin.students.store') }}">
        @csrf
        <input name="user_id" placeholder="User ID" required>
        <input name="class_id" placeholder="Class ID">
        <input name="admission_number" placeholder="Admission number">
        <button type="submit">Create student</button>
    </form>
    <ul>
        @foreach ($students as $student)
            <li>{{ $student->user->name ?? 'Unknown' }} - {{ $student->class->name ?? 'Unassigned' }}</li>
        @endforeach
    </ul>
</body>
</html>
