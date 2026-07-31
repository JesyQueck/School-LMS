<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees</title>
</head>
<body>
    <h1>Fees for {{ $student->admission_no }}</h1>

    @forelse($fees as $fee)
        <div>
            <h3>{{ $fee->feeType->name ?? 'Fee' }}</h3>
            <p>Term: {{ $fee->term->name ?? 'N/A' }}</p>
            <p>Amount: {{ $fee->amount_expected ?? 0 }}</p>
            <p>Status: {{ $fee->status ?? 'N/A' }}</p>
        </div>
    @empty
        <p>No fee records found.</p>
    @endforelse
</body>
</html>
