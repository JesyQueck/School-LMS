<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Structure</title>
</head>
<body>
    <h1>Academic Structure</h1>

    <h2>Create Session</h2>
    <form method="POST" action="{{ route('admin.academic.sessions.store') }}">
        @csrf
        <input name="name" placeholder="Session name" required>
        <input name="start_date" type="date" required>
        <input name="end_date" type="date" required>
        <button type="submit">Create session</button>
    </form>

    <h2>Create Term</h2>
    <form method="POST" action="{{ route('admin.academic.terms.store') }}">
        @csrf
        <input name="academic_session_id" placeholder="Session ID" required>
        <input name="name" placeholder="Term name" required>
        <input name="start_date" type="date" required>
        <input name="end_date" type="date" required>
        <button type="submit">Create term</button>
    </form>

    <h2>Create Subject</h2>
    <form method="POST" action="{{ route('admin.academic.subjects.store') }}">
        @csrf
        <input name="name" placeholder="Subject name" required>
        <button type="submit">Create subject</button>
    </form>

    <h2>Create Class Subject</h2>
    <form method="POST" action="{{ route('admin.academic.class-subjects.store') }}">
        @csrf
        <input name="class_id" placeholder="Class ID" required>
        <input name="subject_id" placeholder="Subject ID" required>
        <label><input type="checkbox" name="is_compulsory" value="1"> Compulsory</label>
        <button type="submit">Create class subject</button>
    </form>

    <h2>Sessions</h2>
    <ul>
        @foreach ($sessions as $session)
            <li>{{ $session->name }} ({{ $session->start_date->toDateString() }} - {{ $session->end_date->toDateString() }})</li>
        @endforeach
    </ul>
</body>
</html>
