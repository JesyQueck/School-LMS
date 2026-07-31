<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Management</title>
</head>
<body>
    <h1>Results Management</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>Submit Result</h2>
    <form method="POST" action="{{ route('admin.results.store') }}">
        @csrf
        <div>
            <label>Student</label>
            <select name="student_id" required>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}">{{ $student->user->name ?? $student->id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Class Subject</label>
            <select name="class_subject_id" required>
                @foreach ($classSubjects as $classSubject)
                    <option value="{{ $classSubject->id }}">{{ $classSubject->class->name }} - {{ $classSubject->subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Term</label>
            <select name="term_id" required>
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>CA Score</label>
            <input type="number" step="0.01" name="ca_score">
        </div>
        <div>
            <label>Exam Score</label>
            <input type="number" step="0.01" name="exam_score">
        </div>
        <div>
            <label>Remark</label>
            <input type="text" name="remark">
        </div>
        <button type="submit">Submit Result</button>
    </form>

    <h2>Existing Results</h2>
    <ul>
        @foreach ($results as $result)
            <li>
                {{ $result->student->user->name ?? $result->student->id }} - {{ $result->classSubject->subject->name }} - {{ $result->term->name }} - {{ $result->total }} - {{ $result->grade }}
                @if (! $result->is_locked)
                    <form method="POST" action="{{ route('admin.results.lock', $result) }}" style="display:inline;">
                        @csrf
                        <button type="submit">Lock</button>
                    </form>
                @else
                    <span>Locked</span>
                @endif
            </li>
        @endforeach
    </ul>
</body>
</html>
