<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        $subjectCount = $reportCard->student->results->where('term_id', $term->id ?? null)->count() ?? 0;

        if ($subjectCount >= 25) {
            $scaleTier = 'super-compact';
        } elseif ($subjectCount >= 18) {
            $scaleTier = 'compact';
        } elseif ($subjectCount >= 15) {
            $scaleTier = 'standard';
        } else {
            $scaleTier = 'relaxed';
        }

        $pageContentHeightMm = 273;
        $fixedBlocksHeightMm = 130;
        $tableHeaderRowMm = 8;
        $availableForRowsMm = max(20, $pageContentHeightMm - $fixedBlocksHeightMm - $tableHeaderRowMm);
        $rowHeightMm = $subjectCount > 0 ? $availableForRowsMm / $subjectCount : 10;
        $rowHeightMm = max(4.0, min(12, $rowHeightMm));
    @endphp
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; color: #000000; background: #e9e9e4; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .font-display { font-family: 'Source Serif 4', Georgia, serif; }
        @page { size: A4; margin: 0; }
        .sheet { width: 210mm; margin: 0 auto; background: #ffffff; padding: 10mm; }
        @media print { body { background: none; } .sheet { margin: 0; box-shadow: none; padding: 10mm; } }

        table.layout { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.layout td { border: none; padding: 0; vertical-align: middle; box-sizing: border-box; }

        .school-header-table { border-bottom: 2px solid #16324F; margin-bottom: 16px; padding-bottom: 12px; table-layout: fixed; }
        .school-header-table td { vertical-align: middle; box-sizing: border-box; }
        .school-logo-cell { width: 60px; }
        .school-logo { width: 48px; height: 48px; border: 2px solid #16324F; border-radius: 50%; text-align: center; }
        .school-logo-text { font-family: 'Source Serif 4', Georgia, serif; font-size: 12px; font-weight: 700; color: #059669; line-height: 44px; }
        .school-center-cell { text-align: center; width: auto; }
        .school-name { font-family: 'Source Serif 4', Georgia, serif; font-size: 16px; font-weight: 700; color: #16324F; margin-bottom: 2px; }
        .school-details { font-size: 10px; color: #6b7280; line-height: 1.4; }
        .school-status-cell { width: 120px; text-align: right; }
        .published-badge { display: inline-block; border: 1px solid #1B6B3A; padding: 3px 10px; font-size: 10px; font-weight: 700; letter-spacing: 0.08em; color: #1B6B3A; background-color: #dcfce7; }

        .title-block { text-align: center; margin-bottom: 16px; }
        .title-block .section-title { font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; color: #059669; letter-spacing: 0.25em; text-transform: uppercase; }
        .title-block .subtitle { font-size: 11px; color: #6b7280; margin-top: 3px; }

        table.info-grid-table { margin-bottom: 16px; table-layout: fixed; }
        table.info-grid-table td { width: 25%; padding: 0 6px; box-sizing: border-box; }
        table.info-grid-table td:first-child { padding-left: 0; }
        table.info-grid-table td:last-child { padding-right: 0; }
        .info-item { border: 1px solid #C9CDD3; padding: 8px 10px; border-radius: 4px; font-size: 13px; }
        .info-item .label { text-transform: uppercase; font-size: 9px; letter-spacing: 0.025em; color: #6b7280; display: block; margin-bottom: 3px; }
        .info-item .value { font-weight: 600; color: #000000; }

        table.results { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 16px; line-height: 1; }
        table.results th { background: #059669; color: #fff; font-weight: 600; font-size: 9px; letter-spacing: 0.04em; text-transform: uppercase; padding: 8px 6px; text-align: left; border: 1px solid #059669; box-sizing: border-box; }
        table.results td { border: 1px solid #C9CDD3; color: #000000; padding: 0 6px; text-align: center; box-sizing: border-box; word-wrap: break-word; overflow-wrap: break-word; word-break: break-word; }
        table.results td.text-left { text-align: left; }
        table.results th.col-subject { width: 28%; }
        table.results th.col-ca, table.results th.col-exam, table.results th.col-total, table.results th.col-grade { width: 10%; }
        table.results th.col-remark { width: 22%; }
        table.results tbody tr.row-even { background: #F4F2EA; }
        table.results tbody tr { page-break-inside: avoid; page-break-after: avoid; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .italic { font-style: italic; }
        .text-red { color: #A3312B; }
        .text-navy { color: #16324F; }

        .tier-relaxed table.results { font-size: 11px; }
        .tier-relaxed table.results th { font-size: 9px; padding: 6px 4px; }
        .tier-relaxed table.results td { padding: 6px 4px; }

        .tier-standard table.results { font-size: 10px; }
        .tier-standard table.results th { font-size: 8px; padding: 4px 4px; }
        .tier-standard table.results td { padding: 4px 4px; }

        .tier-compact table.results { font-size: 9px; }
        .tier-compact table.results th { font-size: 7px; padding: 3px 3px; }
        .tier-compact table.results td { padding: 3px 3px; }

        .tier-super-compact table.results { font-size: 8px; }
        .tier-super-compact table.results th { font-size: 6px; padding: 2px 2px; }
        .tier-super-compact table.results td { padding: 0 2px; }

        .summary-block { font-size: 12px; margin-bottom: 16px; }
        .summary-block p { margin-bottom: 3px; }
        .summary-block strong { font-weight: 600; color: #000000; }
        .summary-block span { font-weight: 500; color: #000000; }

        table.remarks-grid-table { margin-bottom: 16px; table-layout: fixed; }
        table.remarks-grid-table td { width: 50%; padding: 0 6px 12px 0; vertical-align: top; box-sizing: border-box; }
        table.remarks-grid-table td:nth-child(2) { padding-right: 0; padding-left: 6px; }
        table.remarks-grid-table td.full-width { width: 100%; padding-right: 0; padding-left: 0; }
        .remark-card { border: 1px solid #C9CDD3; border-radius: 4px; padding: 10px; }
        .remark-card .section-label { text-transform: uppercase; font-size: 9px; letter-spacing: 0.05em; color: #6b7280; margin: 0 0 6px 0; }
        .remark-card .remark-text { font-size: 11px; font-style: italic; color: #000000; min-height: 32px; word-wrap: break-word; overflow-wrap: break-word; }
        .remark-card .signature-line { font-size: 9px; color: #6b7280; margin-top: 12px; padding-top: 6px; border-top: 1px solid #C9CDD3; }

        .grading-key { border: 1px solid #C9CDD3; border-radius: 4px; padding: 10px; background: #F9F8F3; margin-bottom: 16px; }
        .grading-key .key-content { font-size: 9px; color: #6b7280; line-height: 1.5; }
        .grading-key .key-content span.font-bold { color: #16324F; }
        .grading-key .key-content span.text-red { color: #A3312B; font-weight: 700; }

        .footer { border-top: 1px solid #16324F; padding-top: 8px; font-size: 10px; color: #6b7280; }
        .footer .copyright { text-align: center; margin-top: 4px; }
    </style>
</head>
<body>
<div class="sheet tier-{{ $scaleTier }}">

    <!-- School Header: 3-column table (logo | name+details | status badge) -->
    <table class="layout school-header-table">
        <tr>
            <td class="school-logo-cell">
                <div class="school-logo">
                    <span class="school-logo-text">{{ substr($school->name ?? config('school.name', 'GHS'), 0, 3) }}</span>
                </div>
            </td>
            <td class="school-center-cell">
                <h1 class="school-name">{{ $school->name ?? config('school.name', 'Greenfield High School') }}</h1>
                <p class="school-details">
                    {{ $school->address ?? config('school.address', '123 Education Lane, Victoria Island, Lagos') }}<br>
                    {{ $school->phone ?? config('school.phone', '+234 800 000 0000') }} | {{ $school->email ?? config('school.email', 'info@greenfieldhs.edu') }}
                </p>
            </td>
            <td class="school-status-cell">
                <span class="published-badge">
                    {{ ($reportCard->isPublished() ?? false) ? 'PUBLISHED' : strtoupper($reportCard->status ?? 'DRAFT') }}
                </span>
            </td>
        </tr>
    </table>

    <!-- Title -->
    <div class="title-block">
        <p class="section-title">Terminal Report Sheet</p>
        <p class="subtitle">{{ $term->name ?? 'First Term' }} {{ $term->year ?? date('Y') }} Session</p>
    </div>

    <!-- Student Info: 4-column table -->
    <table class="layout info-grid-table">
        <tr>
            <td>
                <div class="info-item">
                    <span class="label">Name:</span>
                    <span class="value">{{ $reportCard->student->full_name ?? '—' }}</span>
                </div>
            </td>
            <td>
                <div class="info-item">
                    <span class="label">Admission No:</span>
                    <span class="value">{{ $reportCard->student->admission_no ?? '—' }}</span>
                </div>
            </td>
            <td>
                <div class="info-item">
                    <span class="label">Class:</span>
                    <span class="value">{{ $reportCard->student->schoolClass->name ?? '—' }}</span>
                </div>
            </td>
            <td>
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
            </td>
        </tr>
    </table>

    <!-- Results Table: the ONLY flexible section — row height is computed above -->
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
            @forelse($reportCard->student->results->where('term_id', $term->id ?? null) as $index => $result)
                @php
                    $total = ($result->ca_score ?? 0) + ($result->exam_score ?? 0);
                    $grade = $result->grade ?? null;
                    $remark = $result->remark ?? null;

                    if (!$grade) {
                        $grading = (new \App\Services\ResultService())->calculateGrade($total > 0 ? $total : null);
                        $grade = $grading['grade'];
                        $remark = $grading['remark'];
                    }
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'row-even' : '' }}" style="height: {{ $rowHeightMm }}mm;">
                    <td class="text-left">{{ $result->classSubject->subject->name ?? '—' }}</td>
                    <td>{{ $result->ca_score ?? 0 }}</td>
                    <td>{{ $result->exam_score ?? 0 }}</td>
                    <td class="font-semibold">{{ number_format($total, 0) }}</td>
                    <td class="font-bold" style="color: {{ in_array($grade, ['F9', 'D7', 'E8']) ? '#A3312B' : '#16324F' }};">{{ $grade ?? 'N/A' }}</td>
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

    <!-- Remarks: 2-column table, Principal's remark spans full width on its own row -->
    <table class="layout remarks-grid-table">
        <tr>
            <td>
                <div class="remark-card">
                    <span class="section-label">Class Teacher's Remark</span>
                    <p class="remark-text">{{ $reportCard->class_teacher_remark ?? '—' }}</p>
                    <p class="signature-line">Signature: ______________________ &nbsp; Date: ____________</p>
                </div>
            </td>
            <td>
                <div class="remark-card">
                    <span class="section-label">Affective Domain</span>
                    <p class="remark-text">{{ $reportCard->affective_domain ?? '—' }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="remark-card">
                    <span class="section-label">Psychomotor Assessment</span>
                    <p class="remark-text">{{ $reportCard->psychomotor_assessment ?? '—' }}</p>
                </div>
            </td>
            <td>
                <div class="remark-card">
                    <span class="section-label">Health Remarks</span>
                    <p class="remark-text">{{ $reportCard->health_remarks ?? '—' }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="full-width">
                <div class="remark-card">
                    <span class="section-label">Principal's Remark</span>
                    <p class="remark-text">{{ $reportCard->principal_remark ?? '—' }}</p>
                    <p class="signature-line">Signature: ______________________ &nbsp; Date: ____________</p>
                </div>
            </td>
        </tr>
    </table>

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
            &copy; {{ date('Y') }} {{ $school->name ?? config('school.name', 'Greenfield Academy') }}. All rights reserved.
        </div>
    </div>
</div>
</body>
</html>
