<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <ul>
        <li>Classes: {{ $classes }}</li>
        <li>Teachers: {{ $teachers }}</li>
        <li>Students: {{ $students }}</li>
    </ul>
    <p><a href="{{ route('admin.classes') }}">Manage classes</a></p>
    <p><a href="{{ route('admin.teachers') }}">Manage teachers</a></p>
    <p><a href="{{ route('admin.students') }}">Manage students</a></p>
</body>
</html>
