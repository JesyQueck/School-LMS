<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes</title>
</head>
<body>
    <h1>Classes</h1>
    <form method="POST" action="{{ route('admin.classes.store') }}">
        @csrf
        <input name="name" placeholder="Class name" required>
        <button type="submit">Create class</button>
    </form>
    <ul>
        @foreach ($classes as $class)
            <li>{{ $class->name }} - Form teacher: {{ $class->formTeacher?->user?->name ?? 'None' }}</li>
        @endforeach
    </ul>
</body>
</html>
