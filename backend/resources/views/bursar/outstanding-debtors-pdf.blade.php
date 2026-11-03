<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Outstanding Debtors Report</title>
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
        .class-section { margin-top: 24px; page-break-inside: avoid; }
        .class-header { background: #e8f0fe; padding: 8px 12px; border-radius: 4px; margin-top: 16px; }
        .class-title { font-size: 14px; font-weight: 700; color: #1a1a1a; }
        .class-meta { font-size: 10px; color: #666; margin-top: 2px; }
        .class-summary { display: flex; gap: 8px; margin: 8px 0 12px; flex-wrap: wrap; }
        .class-summary-item { flex: 1; min-width: 100px; text-align: center; background: #f9f9f9; padding: 6px; border-radius: 4px; }
        .class-summary-row { display: flex; justify-content: center; align-items: center; gap: 4px; }
        .class-summary-value { font-size: 12px; font-weight: 700; color: #047857; }
        .class-summary-label { font-size: 9px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="center">
        <img src="{{ public_path('images/Logo.webp') }}" alt="{{ config('school.name') }}" style="height: 64px; width: auto; display: block; margin: 0 auto 4px;">
        <div class="school-name">{{ config('school.name', 'School') }}</div>
        <div class="school-detail">{{ config('school.address', '') }}</div>
        <div class="school-detail">{{ config('school.phone', '') }} | {{ config('school.email', '') }}</div>
        <div class="sub">Outstanding Debtors Report</div>
    </div>
    <div class="line"></div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">&#8358;{{ number_format($summary['total_outstanding'], 2) }}</span>
                <span class="summary-label">Total Outstanding</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">{{ $summary['total_students'] }}</span>
                <span class="summary-label">Students Owing</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-row">
                <span class="summary-value">{{ $summary['total_fee_records'] }}</span>
                <span class="summary-label">Fee Records</span>
            </div>
        </div>
    </div>

    @forelse($classSummaries as $classSummary)
        <div class="class-section">
            <div class="class-header">
                <div class="class-title">{{ $classSummary['class'] }}</div>
                <div class="class-meta">{{ $classSummary['students'] }} students • {{ $classSummary['fee_records'] }} fee records</div>
            </div>

            <div class="class-summary">
                <div class="class-summary-item">
                    <div class="class-summary-row">
                        <span class="class-summary-value">&#8358;{{ number_format($classSummary['expected'], 2) }}</span>
                        <span class="class-summary-label">Expected</span>
                    </div>
                </div>
                <div class="class-summary-item">
                    <div class="class-summary-row">
                        <span class="class-summary-value">&#8358;{{ number_format($classSummary['collected'], 2) }}</span>
                        <span class="class-summary-label">Collected</span>
                    </div>
                </div>
                <div class="class-summary-item">
                    <div class="class-summary-row">
                        <span class="class-summary-value">&#8358;{{ number_format($classSummary['outstanding'], 2) }}</span>
                        <span class="class-summary-label">Outstanding</span>
                    </div>
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
                    @forelse($debtors->where('class', $classSummary['class']) as $debtor)
                        <tr>
                            <td>{{ $debtor['student_name'] }}</td>
                            <td>{{ $debtor['admission_no'] }}</td>
                            <td>{{ $debtor['fee_type'] }}</td>
                            <td>&#8358;{{ number_format($debtor['expected'], 2) }}</td>
                            <td>&#8358;{{ number_format($debtor['paid'], 2) }}</td>
                            <td>&#8358;{{ number_format($debtor['outstanding'], 2) }}</td>
                            <td>{{ ucfirst($debtor['status']) }}</td>
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