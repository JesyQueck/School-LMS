<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Daily Collection Report - {{ $term->name }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-weight: normal;
            font-style: normal;
        }
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', 'Segoe UI', system-ui, sans-serif; color: #1a1a1a; padding: 32px; }
        .center { text-align: center; }
        .line { border-top: 1px solid #e5e5e5; margin: 16px 0; }
        .row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
        .label { color: #666; }
        .sub { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 2px; }
        .school-name { font-size: 18px; font-weight: 700; color: #047857; }
        .school-detail { font-size: 10px; color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 10px; }
        th { background: #f5f5f5; padding: 6px; text-align: left; border: 1px solid #ddd; font-weight: 600; }
        td { padding: 6px; border: 1px solid #ddd; }
        .summary-box { display: flex; gap: 8px; background: #f9f9f9; padding: 12px; border-radius: 8px; margin-top: 16px; flex-wrap: wrap; }
        .summary-item { flex: 1; min-width: 120px; text-align: center; }
        .summary-item:not(:last-child) { border-right: 1px solid #e5e5e5; }
        .summary-row { display: flex; justify-content: center; align-items: center; gap: 6px; }
        .summary-value { font-size: 16px; font-weight: 700; color: #047857; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="center">
        <img src="{{ public_path('images/Logo.webp') }}" alt="{{ config('school.name') }}" style="height: 64px; width: auto; display: block; margin: 0 auto 4px;">
        <div class="school-name">{{ config('school.name', 'School') }}</div>
        <div class="school-detail">{{ config('school.address', '') }}</div>
        <div class="school-detail">{{ config('school.phone', '') }} | {{ config('school.email', '') }}</div>
        <div class="sub">Daily Collection Report - {{ $term->name }}</div>
    </div>
    <div class="line"></div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">&#8358;{{ number_format($summary['total_collected'], 2) }}</span>
                <span class="summary-label">Total Collected</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">{{ $summary['total_transactions'] }}</span>
                <span class="summary-label">Transactions</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">
                    @foreach($summary['by_method'] as $method => $data)
                        {{ ucfirst($method) }}: {{ $data['count'] }}{{ $loop->last ? '' : ' | ' }}
                    @endforeach
                </span>
                <span class="summary-label">By Method</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Student</th>
                <th>Admission No</th>
                <th>Class</th>
                <th>Fee Type</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyData as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td>{{ $row['receipt_number'] }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    <td>{{ $row['admission_no'] }}</td>
                    <td>{{ $row['class'] }}</td>
                    <td>{{ $row['fee_type'] }}</td>
                    <td>&#8358;{{ number_format($row['amount_paid'], 2) }}</td>
                    <td>{{ ucfirst($row['payment_method']) }}</td>
                    <td>{{ $row['reference'] ?? '—' }}</td>
                    <td>{{ $row['recorded_by'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="line"></div>
    <div class="row"><span class="label">Generated:</span><span>{{ now()->format('d F Y h:i A') }}</span></div>
</body>
</html>