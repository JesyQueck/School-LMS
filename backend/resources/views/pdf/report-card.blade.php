<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; color: #000000; background: #e9e9e4; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .font-display { font-family: 'Source Serif 4', Georgia, serif; }
        @page { size: A4; margin: 0; }
        .sheet { width: 210mm; min-height: 297mm; margin: 5mm auto; background: #ffffff; position: relative; box-shadow: 0 1px 6px rgba(0,0,0,0.1); padding: 10mm; }
        @media print { body { background: none; } .sheet { margin: 0; box-shadow: none; padding: 10mm; } }

        .school-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; border-bottom: 2px solid #16324F; margin-bottom: 16px; }
        .school-logo { width: 48px; height: 48px; border: 2px solid #16324F; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .school-logo-text { font-family: 'Source Serif 4', Georgia, serif; font-size: 12px; font-weight: 700; color: #059669; }
        .school-center { flex: 1; text-align: center; }
        .school-name { font-family: 'Source Serif 4', Georgia, serif; font-size: 16px; font-weight: 700; color: #16324F; margin-bottom: 2px; }
        .school-details { font-size: 10px; color: #6b7280; line-height: 1.4; }
        .published-badge { display: inline-block; border: 1px solid #1B6B3A; padding: 3px 10px; font-size: 10px; font-weight: 700; letter-spacing: 0.08em; color: #1B6B3A; background-color: #dcfce7; }

        .title-block { text-align: center; margin-bottom: 16px; }
        .title-block .section-title { font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; color: #059669; letter-spacing: 0.25em; text-transform: uppercase; }
        .title-block .subtitle { font-size: 11px; color: #6b7280; margin-top: 3px; }

        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; font-size: 13px; margin-bottom: 16px; }
        .info-item { border: 1px solid #C9CDD3; padding: 8px 10px; border-radius: 4px; }
        .info-item .label { text-transform: uppercase; font-size: 9px; letter-spacing: 0.025em; color: #6b7280; display: block; margin-bottom: 3px; }
        .info-item .value { font-weight: 600; color: #000000; }

        table.results { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px; }
        table.results th { background: #059669; color: #fff; font-weight: 600; font-size: 9px; letter-spacing: 0.04em; text-transform: uppercase; padding: 8px 6px; text-align: left; border: 1px solid #059669; }
        table.results td { border: 1px solid #C9CDD3; color: #000000; padding: 6px 6px; text-align: center; }
        table.results td.text-left { text-align: left; }
        table.results th.col-subject { width: 28%; }
        table.results th.col-ca, table.results th.col-exam, table.results th.col-total, table.results th.col-grade { width: 10%; }
        table.results th.col-remark { width: 22%; }
        table.results tbody tr:nth-child(even) { background: #F4F2EA; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .italic { font-style: italic; }
        .text-red { color: #A3312B; }
        .text-navy { color: #16324F; }

        .summary-block { font-size: 12px; margin-bottom: 16px; }
        .summary-block p { margin-bottom: 3px; }
        .summary-block strong { font-weight: 600; color: #000000; }
        .summary-block span { font-weight: 500; color: #000000; }

        .remarks-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .remark-card { border: 1px solid #C9CDD3; border-radius: 4px; padding: 10px; }
        .remark-card.full-width { grid-column: 1 / -1; }
        .remark-card .section-label { text-transform: uppercase; font-size: 9px; letter-spacing: 0.05em; color: #6b7280; margin: 0 0 6px 0; }
        .remark-card .remark-text { font-size: 11px; font-style: italic; color: #000000; min-height: 32px; }
        .remark-card .signature-line { font-size: 9px; color: #6b7280; margin-top: 12px; padding-top: 6px; border-top: 1px solid #C9CDD3; }

        .grading-key { border: 1px solid #C9CDD3; border-radius: 4px; padding: 10px; background: #F9F8F3; margin-bottom: 16px; }
        .grading-key .key-content { font-size: 9px; color: #6b7280; line-height: 1.5; }
        .grading-key .key-content span.font-bold { color: #16324F; }
        .grading-key .key-content span.text-red { color: #A3312B; font-weight: 700; }

        .footer { border-top: 1px solid #16324F; padding-top: 8px; font-size: 10px; color: #6b7280; }
        .footer .next-term { }
        .footer .copyright { text-align: center; margin-top: 4px; }
    </style>
</head>
<body>
<div class="sheet">
    <!-- School Header: Logo Left, Name Center, Status Right -->
    <div class="school-header">
        <div class="school-logo">
            <span class="school-logo-text">{{ substr($school->name ?? 'GHS', 0, 3) }}</span>
        </div>
        <div class="school-center">
            <h1 class="school-name">{{ $school->name ?? 'Greenfield High School' }}</h1>
            <p class="school-details">
                {{ $school->address ?? '123 Education Lane, Victoria Island, Lagos' }}<br>
                {{ $school->phone ?? '+234 800 000 0000' }} | {{ $school->email ?? 'info@greenfieldhs.edu' }}
            </p>
        </div>
        <div>
            <span class="published-badge">
                {{ ($reportCard->is_published ?? false) ? 'PUBLISHED' : strtoupper($reportCard->status ?? 'DRAFT') }}
            </span>
        </div>
    </div>

    <!-- Title -->
    <div class="title-block">
        <p class="section-title">Terminal Report Sheet</p>
        <p class="subtitle">{{ $term->name ?? 'First Term' }} {{ $term->year ?? date('Y') }} Session</p>
    </div>

    <!-- Student Info Grid -->
    <div class="info-grid">
        <div class="info-item">
            <span class="label">Name:</span>
            <span class="value">{{ $reportCard->student->full_name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="label">Admission No:</span>
            <span class="value">{{ $reportCard->student->admission_no ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="label">Class:</span>
            <span class="value">{{ $reportCard->student->class->name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="label">Date of Birth:</span>
            <span class="value">
                @if($reportCard->student->date_of_birth)
                    {{ \Carbon\Carbon::parse($reportCard->student->date_of_birth)->format('d M Y') }}
                @else
                    —
                @endif
            </span>
        </div>
    </div>

    <!-- Results Table - Full Width, Large -->
    <table class="results">
        <thead>
            <tr>
                <th class="col-subject">Subject</th>
                <th class="col-ca">CA</th>
                <th class="col-exam">Exam</th>
                <th class="col-total">Total</th>
                <th class="col-grade">Grade</th>
                <th class="col-remark">Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportCard->student->results->where('term_id', $term->id ?? null) as $result)
                @php
                    $total = ($result->ca_score ?? 0) + ($result->exam_score ?? 0);
                    $grade = $result->grade ?? null;
                    if (!$grade && $total !== null) {
                        if ($total >= 75) $grade = 'A1';
                        elseif ($total >= 70) $grade = 'B2';
                        elseif ($total >= 65) $grade = 'B3';
                        elseif ($total >= 50) $grade = 'C4';
                        elseif ($total >= 45) $grade = 'D7';
                        elseif ($total >= 40) $grade = 'E8';
                        else $grade = 'F9';
                    }
                    $remarkMap = [
                        'A1' => 'Excellent', 'B2' => 'Very Good', 'B3' => 'Good',
                        'C4' => 'Credit', 'C5' => 'Credit', 'C6' => 'Credit',
                        'D7' => 'Pass', 'E8' => 'Pass', 'F9' => 'Fail'
                    ];
                    $displayGrade = $result->grade ?? $grade ?? 'N/A';
                    $isFail = in_array($displayGrade, ['F9', 'D7', 'E8']);
                    $remark = $result->remark ?? ($remarkMap[$result->grade ?? $grade ?? 'F9'] ?? 'N/A');
                @endphp
                <tr>
                    <td class="text-left" style="padding: 8px 10px;">{{ $result->classSubject->subject->name ?? '—' }}</td>
                    <td>{{ $result->ca_score ?? 0 }}</td>
                    <td>{{ $result->exam_score ?? 0 }}</td>
                    <td class="font-semibold">{{ number_format($total, 0) }}</td>
                    <td class="font-bold" style="color: {{ $isFail ? '#A3312B' : '#16324F' }};">{{ $displayGrade }}</td>
                    <td class="text-left">{{ $remark }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 12px 10px; text-align: center; color: #6b7280;">
                        No results available for this term.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary Stats -->
    <div class="summary-block">
        <p><strong>Position in Class:</strong> {{ $reportCard->position_in_class ?? '—' }} of {{ $reportCard->total_students_in_class ?? '—' }}</p>
        <p><strong>Average Percentage:</strong> {{ number_format($reportCard->student->results->where('term_id', $term->id ?? null)->avg('total') ?? 0, 1) }}%</p>
        @if($reportCard->student->relationLoaded('attendance') && $reportCard->student->attendance && $reportCard->student->attendance->count() > 0)
            @php
                $attTerm = $reportCard->student->attendance->where('term_id', $term->id ?? null);
            @endphp
            @if($attTerm->count() > 0)
            <p><strong>Attendance:</strong> {{ $attTerm->where('status', 'present')->count() }}/{{ $attTerm->count() }} days present</p>
            @endif
        @endif
    </div>

    <!-- Remarks Grid -->
    <div class="remarks-grid">
        <div class="remark-card">
            <span class="section-label">Class Teacher's Remark</span>
            <p class="remark-text">{{ $reportCard->class_teacher_remark ?? '—' }}</p>
            <p class="signature-line">
                Signature: ______________________ &nbsp; Date: ____________
            </p>
        </div>

        <div class="remark-card">
            <span class="section-label">Affective Domain</span>
            <p class="remark-text">{{ $reportCard->affective_domain ?? '—' }}</p>
        </div>

        <div class="remark-card">
            <span class="section-label">Psychomotor Assessment</span>
            <p class="remark-text">{{ $reportCard->psychomotor_assessment ?? '—' }}</p>
        </div>

        <div class="remark-card">
            <span class="section-label">Health Remarks</span>
            <p class="remark-text">{{ $reportCard->health_remarks ?? '—' }}</p>
        </div>

        <div class="remark-card full-width">
            <span class="section-label">Principal's Remark</span>
            <p class="remark-text">{{ $reportCard->principal_remark ?? '—' }}</p>
            <p class="signature-line">
                Signature: ______________________ &nbsp; Date: ____________
            </p>
        </div>
    </div>

    <!-- Grading Key -->
    <div class="grading-key">
        <div class="key-content">
            <span class="font-bold">Grading Key:</span>
            A1 (75-100) Excellent &middot; B2 (70-74) V.Good &middot; B3 (65-69) Good &middot;
            C4-C6 (50-64) Credit &middot; D7 (45-49) Pass &middot; E8 (40-44) Pass &middot;
            <span class="text-red">F9 (0-39) Fail</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="next-term">
            Next Term Begins: <span class="font-semibold" style="color: #000000;">{{ $reportCard->next_term_begins ? \Carbon\Carbon::parse($reportCard->next_term_begins)->format('d M Y') : 'TBA' }}</span>
        </div>
        <div class="copyright">
            &copy; {{ date('Y') }} {{ $school->name ?? 'Greenfield Academy' }}. All rights reserved.
        </div>
    </div>
</div>
</body>
</html>