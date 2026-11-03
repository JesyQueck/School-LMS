<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student Statement - {{ $student->full_name }}</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 11px; }
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
        <div class="sub">Student Statement</div>
    </div>
    <div class="line"></div>

    <div class="row"><span class="label">Student:</span><span>{{ $student->full_name }}</span></div>
    <div class="row"><span class="label">Admission No:</span><span>{{ $student->admission_no }}</span></div>
    <div class="row"><span class="label">Class:</span><span>{{ $student->schoolClass->name ?? 'N/A' }}</span></div>
    <div class="line"></div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">&#8358;{{ number_format($summary['total_expected'], 2) }}</span>
                <span class="summary-label">Total Expected</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">&#8358;{{ number_format($summary['total_paid'], 2) }}</span>
                <span class="summary-label">Total Paid</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">&#8358;{{ number_format($summary['total_outstanding'], 2) }}</span>
                <span class="summary-label">Outstanding</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fee Type</th>
                <th>Term</th>
                <th>Expected</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statement as $fee)
                <tr>
                    <td>{{ $fee['fee_type'] }}</td>
                    <td>{{ $fee['term'] }}</td>
                    <td>&#8358;{{ number_format($fee['expected'], 2) }}</td>
                    <td>&#8358;{{ number_format($fee['paid'], 2) }}</td>
                    <td>&#8358;{{ number_format($fee['outstanding'], 2) }}</td>
                    <td>{{ ucfirst($fee['status']) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($statement->flatMap(fn ($f) => $f['payments'])->count() > 0)
        <div class="line"></div>
        <div class="sub">Payment History</div>
        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Receipt No</th>
                    <th>Fee Type</th>
                    <th>Term</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement as $fee)
                    @foreach($fee['payments'] as $payment)
                        <tr>
                            <td>{{ $payment['date'] }}</td>
                            <td>{{ $payment['receipt_number'] }}</td>
                            <td>{{ $fee['fee_type'] }}</td>
                            <td>{{ $fee['term'] }}</td>
                            <td>&#8358;{{ number_format($payment['amount'], 2) }}</td>
                            <td>{{ ucfirst($payment['method']) }}</td>
                            <td>{{ $payment['reference'] ?? '—' }}</td>
                            <td>{{ $payment['recorded_by'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="line"></div>
    <div class="row"><span class="label">Generated:</span><span>{{ now()->format('d F Y h:i A') }}</span></div>
</body>
</html>