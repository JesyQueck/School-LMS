<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
</head>
<body>
    <h1>Teacher Dashboard</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>Your Assignments</h2>
    <ul>
        @foreach ($assignments as $assignment)
            <li>{{ $assignment->classSubject->class->name }} - {{ $assignment->classSubject->subject->name }}</li>
        @endforeach
    </ul>

    <h2>Record Attendance</h2>
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <div>
            <label>Student ID</label>
            <input type="number" name="student_id" required>
        </div>
        <div>
            <label>Class ID</label>
            <input type="number" name="class_id" required>
        </div>
        <div>
            <label>Term ID</label>
            <input type="number" name="term_id" required>
        </div>
        <div>
            <label>Date</label>
            <input type="date" name="date" required>
        </div>
        <div>
            <label>Status</label>
            <input type="text" name="status" required>
        </div>
        <button type="submit">Save Attendance</button>
    </form>
</body>
</html>
