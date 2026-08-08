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
        .sheet { width: 210mm; min-height: 297mm; margin: 10mm auto; background: #FDFCF8; position: relative; box-shadow: 0 4px 24px rgba(0,0,0,0.15); padding: 12mm; }
        @media print { body { background: none; } .sheet { margin: 0; box-shadow: none; padding: 12mm; } }
        
        .school-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; border-bottom: 2px solid #16324F; margin-bottom: 16px; }
        .school-left { display: flex; align-items: center; gap: 12px; }
        .school-logo { width: 48px; height: 48px; border: 2px solid #16324F; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .school-logo-text { font-family: 'Source Serif 4', Georgia, serif; font-size: 10px; font-weight: 700; color: #059669; }
        .school-name { font-family: 'Source Serif 4', Georgia, serif; font-size: 16px; font-weight: 700; color: #16324F; }
        .school-details { font-size: 11px; color: #6b7280; line-height: 1.3; }
        .published-badge { display: inline-block; border: 1px solid #1B6B3A; padding: 2px 10px; font-size: 10px; font-weight: 700; letter-spacing: 0.08em; color: #1B6B3A; background-color: #dcfce7; }
        
        .title-block { text-align: center; margin-bottom: 20px; }
        .title-block .section-title { font-family: 'Inter', sans-serif; font-size: 18px; font-weight: 700; color: #059669; letter-spacing: 0.25em; text-transform: uppercase; }
        .title-block .subtitle { font-size: 12px; color: #6b7280; margin-top: 4px; }
        
        .student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 24px; font-size: 13px; margin-bottom: 20px; }
        .info-row { display: flex; align-items: baseline; gap: 8px; }
        .info-label { text-transform: uppercase; font-size: 10px; letter-spacing: 0.025em; color: #6b7280; min-width: 80px; }
        .info-value { font-weight: 600; color: #000000; }
        
        table.results th { background: #059669; color: #fff; font-weight: 600; font-size: 10px; letter-spacing: 0.04em; text-transform: uppercase; }
        table.results th.text-left { text-align: left; }
        table.results td { border: 1px solid #C9CDD3; color: #000000; }
        table.results th { border: 1px solid #C9CDD3; }
        table.results tbody tr:nth-child(even) { background: #F4F2EA; }
        table.results td.text-center { text-align: center; }
        .results-col-subject { width: 25%; padding: 8px 10px; }
        .results-col-num { width: 12%; padding: 8px 6px; text-align: center; }
        .results-col-remark { width: 25%; padding: 8px 10px; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .italic { font-style: italic; }
        
        .summary-block { font-size: 13px; margin-bottom: 20px; }
        .summary-block p { margin-bottom: 4px; }
        .summary-block strong { font-weight: 600; color: #000000; }
        
        .remark-section { border: 1px solid #C9CDD3; border-radius: 4px; padding: 12px; margin-bottom: 12px; }
        .remark-section p.section-label { text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em; color: #6b7280; margin: 0 0 8px 0; }
        .remark-section p.remark-text { font-size: 12px; font-style: italic; color: #000000; min-height: 36px; }
        .remark-section.no-border { border: 1px solid #C9CDD3; border-radius: 4px; padding: 12px; margin-bottom: 12px; }
        
        .signature-line { font-size: 10px; color: #6b7280; margin-top: 16px; padding-top: 8px; border-top: 1px solid #C9CDD3; }
        
        .grading-key { border: 1px solid #C9CDD3; border-radius: 4px; padding: 12px; background: #F9F8F3; margin-bottom: 20px; }
        .grading-key .key-label { font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #16324F; }
        .grading-key .key-item { font-size: 9px; color: #6b7280; }
        .grading-key .key-item span.text-red { color: #A3312B; font-weight: 700; }
        .grading-key .key-item span { margin-right: 4px; }
        
        .footer { border-top: 1px solid #16324F; padding-top: 8px; font-size: 10px; color: #6b7280; }
        .footer .next-term { }
        .footer .copyright { text-align: center; }
    </style>
</head>
<body>
<div class="sheet">
    <!-- School Header -->
    <div class="school-header">
        <div class="school-left">
            <div class="school-logo">
                <span class="school-logo-text">{{ substr($school->name ?? 'GHS', 0, 3) }}</span>
            </div>
            <div>
                <h1 class="school-name">{{ $school->name ?? 'Greenfield High School' }}</h1>
                <p class="school-details">
                    {{ $school->address ?? '123 Education Lane, Victoria Island, Lagos' }}<br>
                    {{ $school->phone ?? '+234 800 000 0000' }} | {{ $school->email ?? 'info@greenfieldhs.edu' }}
                </p>
            </div>
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

    <!-- Student Info -->
    <div class="student-info">
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value">{{ $reportCard->student->full_name ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Admission No:</span>
            <span class="info-value">{{ $reportCard->student->admission_no ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Class:</span>
            <span class="info-value">{{ $reportCard->student->class->name ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date of Birth:</span>
            <span class="info-value">
                @if($reportCard->student->date_of_birth)
                    {{ \Carbon\Carbon::parse($reportCard->student->date_of_birth)->format('d M Y') }}
                @else
                    —
                @endif
            </span>
        </div>
    </div>

    <!-- Results Table -->
    <table class="results w-full border-collapse" style="margin-bottom: 20px; font-size: 12px;">
        <thead>
            <tr>
                <th class="text-left results-col-subject">Subject</th>
                <th class="results-col-num">CA</th>
                <th class="results-col-num">Exam</th>
                <th class="results-col-num">Total</th>
                <th class="results-col-num">Grade</th>
                <th class="text-left results-col-remark">Remark</th>
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
                    <td class="results-col-subject" style="padding: 6px 10px;">{{ $result->classSubject->subject->name ?? '—' }}</td>
                    <td class="results-col-num" style="padding: 6px 4px; text-align: center;">{{ $result->ca_score ?? 0 }}</td>
                    <td class="results-col-num" style="padding: 6px 4px; text-align: center;">{{ $result->exam_score ?? 0 }}</td>
                    <td class="results-col-num" style="padding: 6px 4px; text-align: center; font-weight: 600;">{{ number_format($total, 0) }}</td>
                    <td class="results-col-num" style="padding: 6px 4px; text-align: center; font-weight: 700; color: {{ $isFail ? '#A3312B' : '#16324F' }};">{{ $displayGrade }}</td>
                    <td class="results-col-remark" style="padding: 6px 10px;">{{ $remark }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 8px 10px; text-align: center; color: #6b7280;">
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

    <!-- Remarks Sections -->
    <div class="remark-section">
        <p class="section-label">Class Teacher's Remark</p>
        <p class="remark-text">{{ $reportCard->class_teacher_remark ?? '—' }}</p>
        <p class="signature-line">
            Signature: ______________________ &nbsp; Date: ____________
        </p>
    </div>

    <div class="remark-section">
        <p class="section-label">Affective Domain</p>
        <p class="remark-text">{{ $reportCard->affective_domain ?? '—' }}</p>
    </div>

    <div class="remark-section">
        <p class="section-label">Psychomotor Assessment</p>
        <p class="remark-text">{{ $reportCard->psychomotor_assessment ?? '—' }}</p>
    </div>

    <div class="remark-section">
        <p class="section-label">Health Remarks</p>
        <p class="remark-text">{{ $reportCard->health_remarks ?? '—' }}</p>
    </div>

    <div class="remark-section">
        <p class="section-label">Principal's Remark</p>
        <p class="remark-text">{{ $reportCard->principal_remark ?? '—' }}</p>
        <p class="signature-line">
            Signature: ______________________ &nbsp; Date: ____________
        </p>
    </div>

    <!-- Grading Key -->
    <div class="grading-key">
        <div class="flex flex-wrap items-center gap-1" style="font-size: 9px; color: #6b7280;">
            <span class="key-label">Grading Key:</span>
            <span class="key-item">A1 (75-100) Excellent <span>&middot;</span></span>
            <span class="key-item">B2 (70-74) V.Good <span>&middot;</span></span>
            <span class="key-item">B3 (65-69) Good <span>&middot;</span></span>
            <span class="key-item">C4-C6 (50-64) Credit <span>&middot;</span></span>
            <span class="key-item">D7 (45-49) Pass <span>&middot;</span></span>
            <span class="key-item">E8 (40-44) Pass <span>&middot;</span></span>
            <span class="key-item text-red">F9 (0-39) Fail</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>
            Next Term Begins: <span class="font-semibold" style="color: #000000;">{{ $reportCard->next_term_begins ? \Carbon\Carbon::parse($reportCard->next_term_begins)->format('d M Y') : 'TBA' }}</span>
        </div>
        <div class="copyright" style="text-align: center;">
            &copy; {{ date('Y') }} {{ $school->name ?? 'Greenfield Academy' }}. All rights reserved.
        </div>
    </div>
</div>
</body>
</html>