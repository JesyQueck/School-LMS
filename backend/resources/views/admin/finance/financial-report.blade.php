<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: 'Helvetica Neue', 'Segoe UI', system-ui, sans-serif; color: #1a1a1a; padding: 32px; }
        .center { text-align: center; }
        .line { border-top: 1px solid #e5e5e5; margin: 16px 0; }
        .row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
        .label { color: #666; }
        .sub { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 11px; }
        th { background: #f5f5f5; padding: 6px; text-align: left; border: 1px solid #ddd; font-weight: 600; }
        td { padding: 6px; border: 1px solid #ddd; }
        .summary-box { display: flex; justify-content: space-around; background: #f9f9f9; padding: 12px; border-radius: 8px; margin-top: 16px; }
        .summary-item { text-align: center; flex: 1; }
        .summary-item:not(:last-child) { border-right: 1px solid #e5e5e5; }
        .summary-value { font-size: 16px; font-weight: 700; color: #047857; }
        .summary-label { font-size: 11px; color: #666; text-transform: uppercase; }
        .class-section { margin-top: 24px; page-break-inside: avoid; }
        .class-header { background: #e8f0fe; padding: 8px 12px; border-radius: 4px; margin-top: 16px; }
        .class-title { font-size: 14px; font-weight: 700; color: #1a1a1a; }
        .class-meta { font-size: 10px; color: #666; margin-top: 2px; }
        .class-summary { display: flex; gap: 8px; margin: 8px 0 12px; flex-wrap: wrap; }
        .class-summary-item { flex: 1; min-width: 100px; text-align: center; background: #f9f9f9; padding: 6px; border-radius: 4px; }
        .class-summary-value { font-size: 12px; font-weight: 700; color: #047857; }
        .class-summary-label { font-size: 9px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="center">
        <img src="{{ public_path('images/Logo.webp') }}" alt="Greenfield Academy" style="height: 64px; width: auto; display: block; margin: 0 auto 4px;">
        <div class="sub">Financial Report</div>
    </div>
    <div class="line"></div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-value">&#8358;{{ number_format($summary['expected'], 2) }}</div>
            <div class="summary-label">Total Expected</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">&#8358;{{ number_format($summary['collected'], 2) }}</div>
            <div class="summary-label">Total Collected</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">&#8358;{{ number_format($summary['outstanding'], 2) }}</div>
            <div class="summary-label">Total Outstanding</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $summary['total_fees'] }}</div>
            <div class="summary-label">Records</div>
        </div>
    </div>

    @forelse($classSummaries as $classSummary)
        <div class="class-section">
            <div class="class-header">
                <div class="class-title">{{ $classSummary['class'] }}</div>
                <div class="class-meta">{{ $classSummary['students'] }} students • {{ $classSummary['fees'] }} fee records</div>
            </div>

            <div class="class-summary">
                <div class="class-summary-item">
                    <div class="class-summary-value">&#8358;{{ number_format($classSummary['expected'], 2) }}</div>
                    <div class="class-summary-label">Expected</div>
                </div>
                <div class="class-summary-item">
                    <div class="class-summary-value">&#8358;{{ number_format($classSummary['collected'], 2) }}</div>
                    <div class="class-summary-label">Collected</div>
                </div>
                <div class="class-summary-item">
                    <div class="class-summary-value">&#8358;{{ number_format($classSummary['outstanding'], 2) }}</div>
                    <div class="class-summary-label">Outstanding</div>
                </div>
                <div class="class-summary-item">
                    <div class="class-summary-value">{{ $classSummary['collection_rate'] }}%</div>
                    <div class="class-summary-label">Collection Rate</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Admission No</th>
                        <th>Fee</th>
                        <th>Expected</th>
                        <th>Paid</th>
                        <th>Outstanding</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classSummary['records'] as $item)
                        <tr>
                            <td>{{ $item['student_name'] }}</td>
                            <td>{{ $item['admission_no'] }}</td>
                            <td>{{ $item['fee_type'] }}</td>
                            <td>&#8358;{{ number_format($item['expected'], 2) }}</td>
                            <td>&#8358;{{ number_format($item['paid'], 2) }}</td>
                            <td>&#8358;{{ number_format($item['outstanding'], 2) }}</td>
                            <td>{{ ucfirst($item['status']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="center">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <div class="line"></div>
        <p class="center" style="font-size: 12px; color: #888;">No records found.</p>
    @endempty

    <div class="line"></div>
    <div class="row"><span class="label">Generated:</span><span>{{ now()->format('d F Y h:i A') }}</span></div>
</body>
</html>
