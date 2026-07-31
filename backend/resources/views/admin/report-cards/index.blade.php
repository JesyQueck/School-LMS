<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Cards</title>
</head>
<body>
    <h1>Report Cards</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>Create Report Card</h2>
    <form method="POST" action="{{ route('admin.report-cards.store') }}">
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
            <label>Term</label>
            <select name="term_id" required>
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Class Teacher Remark</label>
            <textarea name="class_teacher_remark"></textarea>
        </div>
        <div>
            <label>Principal Remark</label>
            <textarea name="principal_remark"></textarea>
        </div>
        <div>
            <label>Position in Class</label>
            <input type="number" name="position_in_class">
        </div>
        <div>
            <label>Total Students in Class</label>
            <input type="number" name="total_students_in_class">
        </div>
        <div>
            <label>Next Term Begins</label>
            <input type="date" name="next_term_begins">
        </div>
        <button type="submit">Create Report Card</button>
    </form>

    <h2>Existing Report Cards</h2>
    <ul>
        @foreach ($reportCards as $reportCard)
            <li>
                {{ $reportCard->student->user->name ?? $reportCard->student->id }} - {{ $reportCard->term->name }}
                @if (! $reportCard->is_published)
                    <form method="POST" action="{{ route('admin.report-cards.publish', $reportCard) }}" style="display:inline;">
                        @csrf
                        <button type="submit">Publish</button>
                    </form>
                @else
                    <span>Published</span>
                @endif
            </li>
        @endforeach
    </ul>
</body>
</html>
