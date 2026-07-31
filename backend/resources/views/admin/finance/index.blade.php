<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management</title>
</head>
<body>
    <h1>Finance Management</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>Create Fee Type</h2>
    <form method="POST" action="{{ route('admin.finance.fee-types.store') }}">
        @csrf
        <div>
            <label>Name</label>
            <input type="text" name="name" required>
        </div>
        <div>
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" required>
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
            <label>Class</label>
            <select name="class_id">
                <option value="">All classes</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit">Save Fee Type</button>
    </form>

    <h2>Create Student Fee</h2>
    <form method="POST" action="{{ route('admin.finance.student-fees.store') }}">
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
            <label>Fee Type</label>
            <select name="fee_type_id" required>
                @foreach ($feeTypes as $feeType)
                    <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
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
            <label>Amount Expected</label>
            <input type="number" step="0.01" name="amount_expected" required>
        </div>
        <button type="submit">Save Student Fee</button>
    </form>

    <h2>Record Payment</h2>
    <form method="POST" action="{{ route('admin.finance.payments.store') }}">
        @csrf
        <div>
            <label>Student Fee</label>
            <select name="student_fee_id" required>
                @foreach ($studentFees as $studentFee)
                    <option value="{{ $studentFee->id }}">{{ $studentFee->student->user->name ?? $studentFee->student->id }} - {{ $studentFee->feeType->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Receipt Number</label>
            <input type="text" name="receipt_number" required>
        </div>
        <div>
            <label>Amount Paid</label>
            <input type="number" step="0.01" name="amount_paid" required>
        </div>
        <div>
            <label>Payment Method</label>
            <input type="text" name="payment_method" value="cash">
        </div>
        <div>
            <label>Payment Date</label>
            <input type="date" name="payment_date" required>
        </div>
        <button type="submit">Record Payment</button>
    </form>
</body>
</html>
