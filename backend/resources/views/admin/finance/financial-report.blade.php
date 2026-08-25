<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; color: #1a1a1a; padding: 32px; }
        .center { text-align: center; }
        .line { border-top: 1px solid #e5e5e5; margin: 16px 0; }
        .row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
        .label { color: #666; }
        .brand { font-size: 22px; font-weight: 700; }
        .sub { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 11px; }
        th { background: #f5f5f5; padding: 6px; text-align: left; border: 1px solid #ddd; font-weight: 600; }
        td { padding: 6px; border: 1px solid #ddd; }
        .summary-box { display: flex; justify-content: space-around; background: #f9f9f9; padding: 12px; border-radius: 8px; margin-top: 16px; }
        .summary-item { text-align: center; }
        .summary-value { font-size: 18px; font-weight: 700; color: #047857; }
        .summary-label { font-size: 11px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="center">
        <div class="brand">GREENFIELD ACADEMY</div>
        <div class="sub">Financial Report</div>
    </div>
    <div class="line"></div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-value">₦{{ number_format($summary['expected'], 2) }}</div>
            <div class="summary-label">Total Expected</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">₦{{ number_format($summary['collected'], 2) }}</div>
            <div class="summary-label">Total Collected</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">₦{{ number_format($summary['outstanding'], 2) }}</div>
            <div class="summary-label">Total Outstanding</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $summary['total_fees'] }}</div>
            <div class="summary-label">Records</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Admission No</th>
                <th>Class</th>
                <th>Fee</th>
                <th>Expected</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $item)
                <tr>
                    <td>{{ $item['student_name'] }}</td>
                    <td>{{ $item['admission_no'] }}</td>
                    <td>{{ $item['class'] }}</td>
                    <td>{{ $item['fee_type'] }}</td>
                    <td>₦{{ number_format($item['expected'], 2) }}</td>
                    <td>₦{{ number_format($item['paid'], 2) }}</td>
                    <td>₦{{ number_format($item['outstanding'], 2) }}</td>
                    <td>{{ ucfirst($item['status']) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="line"></div>
    <div class="row"><span class="label">Generated:</span><span>{{ now()->format('d F Y h:i A') }}</span></div>
</body>
</html>
