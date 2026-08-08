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
        .sheet { width: 210mm; min-height: 297mm; margin: 10mm auto; background: #FDFCF8; position: relative; box-shadow: 0 4px 24px rgba(0,0,0,0.15); }
        @media print { body { background: none; } .sheet { margin: 0; box-shadow: none; } }
        .frame { position: absolute; inset: 8mm; border: 2px solid #16324F; pointer-events: none; }
        .frame::before { content: ""; position: absolute; inset: 4px; border: 1px solid #B8860B; }
        .corner { position: absolute; width: 10mm; height: 10mm; border: 2px solid #B8860B; }
        .corner-tl { top: -1px; left: -1px; border-right: none; border-bottom: none; }
        .corner-tr { top: -1px; right: -1px; border-left: none; border-bottom: none; }
        .corner-bl { bottom: -1px; left: -1px; border-right: none; border-top: none; }
        .corner-br { bottom: -1px; right: -1px; border-left: none; border-top: none; }
        .content { position: relative; padding: 16mm 15mm; }
        table.results th { background: #059669; color: white; font-weight: 600; letter-spacing: 0.04em; }
        table.results tbody tr:nth-child(even) { background: #F4F2EA; }
        table.results td, table.results th { border: 1px solid #C9CDD3; color: #000000; }
        .badge { border: 1px solid currentColor; padding: 2px 10px; font-size: 10px; letter-spacing: 0.08em; font-weight: 700; }
        .text-green { color: #059669; }
        .text-navy { color: #16324F; }
        .text-red { color: #A3312B; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .text-xs { font-size: 10px; }
        .text-sm { font-size: 12px; }
        .text-[9px] { font-size: 9px; }
        .text-[10px] { font-size: 10px; }
        .text-[11px] { font-size: 11px; }
        .text-[12px] { font-size: 12px; }
        .text-[13px] { font-size: 13px; }
        .text-base { font-size: 14px; }
        .text-lg { font-size: 16px; }
        .text-xl { font-size: 18px; }
        .text-2xl { font-size: 20px; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-600 { color: #4b5563; }
        .border-b { border-bottom: 1px solid #C9CDD3; }
        .border-t { border-top: 1px solid #C9CDD3; }
        .border-l { border-left: 1px solid #C9CDD3; }
        .border-r { border-right: 1px solid #C9CDD3; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-5 { margin-bottom: 1.25rem; }
        .mt-0 { margin-top: 0; }
        .mt-0\\.5 { margin-top: 0.125rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-4 { margin-top: 1rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .py-1\\.5 { padding-top: 0.375rem; padding-bottom: 0.375rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .py-5 { padding-top: 1.25rem; padding-bottom: 1.25rem; }
        .pb-1 { padding-bottom: 0.25rem; }
        .pb-3 { padding-bottom: 0.75rem; }
        .pb-4 { padding-bottom: 1rem; }
        .pb-5 { padding-bottom: 1.25rem; }
        .pt-1 { padding-top: 0.25rem; }
        .pt-2 { padding-top: 0.5rem; }
        .pt-3 { padding-top: 0.75rem; }
        .pt-4 { padding-top: 1rem; }
        .pt-5 { padding-top: 1.25rem; }
        .pb-2 { padding-bottom: 0.5rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .items-center { align-items: center; }
        .items-start { align-items: flex-start; }
        .items-end { align-items: flex-end; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .flex-row { flex-direction: row; }
        .flex-col { flex-direction: column; }
        .flex { display: flex; }
        .flex-1 { flex: 1 1 0%; }
        .flex-shrink-0 { flex-shrink: 0; }
        .flex-wrap { flex-wrap: wrap; }
        .flex-col > div { display: block; }
        .hidden { display: none; }
        .rounded-full { border-radius: 9999px; }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-full { border-radius: 9999px; }
        .inline-flex { display: inline-flex; }
        .block { display: block; }
        .uppercase { text-transform: uppercase; }
        .tracking-wide { letter-spacing: 0.025em; }
        .tracking-\\[0\\.25em\\] { letter-spacing: 0.25em; }
        .font-display-text { font-family: 'Source Serif 4', Georgia, serif; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .divide-y > * + * { border-top: 1px solid #e5e7eb; }
        .border { border: 1px solid #CBD5E1; }
        .border-2 { border: 2px solid #CBD5E1; }
        .border-t-2 { border-top-width: 2px; }
        .border-b-2 { border-bottom-width: 2px; }
        .border-neutral-200 { border-color: #e5e7eb; }
        .border-warning-200 { border-color: #fcd34d; }
        .bg-neutral-50 { background-color: #f9fafb; }
        .bg-neutral-100 { background-color: #f3f4f6; }
        .bg-white { background-color: #ffffff; }
        .bg-success-100 { background-color: #dcfce7; }
        .bg-success-900\\/30 { background-color: rgba(27, 94, 31, 0.3); }
        .bg-warning-100 { background-color: #fef3c7; }
        .bg-warning-900\\/30 { background-color: rgba(146, 64, 4, 0.3); }
        .bg-danger-100 { background-color: #fee2e2; }
        .bg-danger-900\\/30 { background-color: rgba(139, 0, 0, 0.3); }
        .bg-primary-100 { background-color: #e0f2fe; }
        .bg-primary-900\\/30 { background-color: rgba(14, 165, 233, 0.3); }
        .bg-primary-500 { background-color: #0ea5e9; }
        .bg-primary-600 { background-color: #0287ce; }
        .bg-green { background-color: #059669; }
        .text-white { color: #ffffff; }
        .text-warning-700 { color: #92400e; }
        .text-warning-300 { color: #fcd34d; }
        .text-danger-700 { color: #b91c1c; }
        .text-danger-300 { color: #fca5a5; }
        .text-danger-500 { color: #ef4444; }
        .text-success-600 { color: #16a34a; }
        .text-success-700 { color: #15803d; }
        .text-success-300 { color: #86efac; }
        .bg-success-500 { background-color: #22c55e; }
        .text-info-500 { color: #3b82f6; }
        .bg-info-500 { background-color: #3b82f6; }
        .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06); }
        .ring-2 { box-shadow: 0 0 0 2px currentColor; }
        .focus\\:ring-primary { outline: 2px solid #0287ce; }
        .transition-colors { transition-property: background-color, border-color; transition-duration: 0.15s; }
        .duration-150 { transition-duration: 150ms; }
        .ease-in-out { transition-timing-function: ease-in-out; }
        .cursor-pointer { cursor: pointer; }
        .leading-tight { line-height: 1.15; }
        .tracking-tight { letter-spacing: -0.025em; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="frame">
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>
    </div>

    <div class="content">
        <div class="flex items-center justify-between gap-4 pb-4 border-b-2" style="border-color: #16324F;">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style="border: 2px solid #16324F;">
                    <span class="font-display text-xs font-bold text-center leading-tight" style="color: #059669;">{{ substr($school->name ?? 'GHS', 0, 3) }}</span>
                </div>
                <div>
                    <h1 class="font-display text-lg font-bold tracking-wide" style="color: #16324F;">{{ $school->name ?? 'Greenfield Heights School' }}</h1>
                    <p class="text-[11px] text-gray-600 mt-0.5">
                        {{ $school->address ?? '123 Learning Street, Lagos, Nigeria' }}<br>
                        {{ $school->phone ?? '0800-GHS-CALL' }}
                    </p>
                </div>
            </div>
            
            <div class="text-right">
                <span class="badge" style="{{ ($reportCard->is_published ?? false) ? 'color: #15803d' : 'color: #A3312B' }};">
                    {{ ($reportCard->is_published ?? false) ? 'PUBLISHED' : strtoupper($reportCard->status ?? 'DRAFT') }}
                </span>
            </div>
        </div>

        <div class="text-center mt-4 mb-5">
            <p class="uppercase text-sm font-bold" style="color: #059669;">
                Terminal Report Sheet
            </p>
            <p class="text-xs text-gray-600 mt-1">
                {{ $term->name ?? 'First Term' }} {{ $term->year ?? date('Y') }} Session
            </p>
        </div>

        <div class="flex flex-col gap-2 text-[13px] mb-6">
            <div class="flex items-start">
                <span class="text-gray-500 uppercase text-[10px] tracking-wide flex-shrink-0 w-20">Name:</span>
                <span class="flex-1 font-semibold" style="color: #000000;">{{ $reportCard->student->full_name ?? '—' }}</span>
            </div>
            <div class="flex items-start">
                <span class="text-gray-500 uppercase text-[10px] tracking-wide flex-shrink-0 w-20">Admission No:</span>
                <span class="flex-1 font-semibold" style="color: #000000;">{{ $reportCard->student->admission_no ?? '—' }}</span>
            </div>
            <div class="flex items-start">
                <span class="text-gray-500 uppercase text-[10px] tracking-wide flex-shrink-0 w-20">Class:</span>
                <span class="flex-1 font-semibold" style="color: #000000;">{{ $reportCard->student->class->name ?? '—' }}</span>
            </div>
            <div class="flex items-start">
                <span class="text-gray-500 uppercase text-[10px] tracking-wide flex-shrink-0 w-20">Date of Birth:</span>
                <span class="flex-1 font-semibold" style="color: #000000;">
                    @if($reportCard->student->date_of_birth)
                        {{ \Carbon\Carbon::parse($reportCard->student->date_of_birth)->format('d M Y') }}
                    @else
                        —
                    @endif
                </span>
            </div>
        </div>

        <table class="results w-full border-collapse text-[12px] mb-4">
            <thead>
                <tr>
                    <th class="text-left py-2 px-3" style="background: #059669; color: #fff;">Subject</th>
                    <th class="py-2 px-2 w-16" style="background: #059669; color: #fff;">CA</th>
                    <th class="py-2 px-2 w-16" style="background: #059669; color: #fff;">Exam</th>
                    <th class="py-2 px-2 w-16" style="background: #059669; color: #fff;">Total</th>
                    <th class="py-2 px-2 w-16" style="background: #059669; color: #fff;">Grade</th>
                    <th class="text-left py-2 px-3" style="background: #059669; color: #fff;">Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportCard->student->results->where('term_id', $term->id ?? null) as $result)
                    @php
                        $total = $result->ca_score + $result->exam_score;
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
                        $remark = $result->remark ?? ($remarkMap[$grade ?? ''] ?? $remarkMap[$result->grade ?? 'F9'] ?? 'N/A');
                    @endphp
                    <tr>
                        <td class="py-1.5 px-3">{{ $result->classSubject->subject->name ?? '—' }}</td>
                        <td class="py-1.5 px-2 text-center">{{ $result->ca_score ?? 0 }}</td>
                        <td class="py-1.5 px-2 text-center">{{ $result->exam_score ?? 0 }}</td>
                        <td class="py-1.5 px-2 text-center font-semibold">{{ $total ?? 'N/A' }}</td>
                        <td class="py-1.5 px-2 text-center font-bold"
                            style="color: {{ in_array($result->grade ?? $grade, ['F9']) ? '#A3312B' : '#16324F' }};">
                            {{ $result->grade ?? $grade ?? 'N/A' }}
                        </td>
                        <td class="py-1.5 px-3">{{ $remark }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-2 px-3 text-center" style="color: #6b7280;">
                            No results available for this term.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mb-4">
            <p class="text-sm mb-1"><strong>Position in Class:</strong> {{ $reportCard->position_in_class ?? '—' }} of {{ $reportCard->total_students_in_class ?? '—' }}</p>
            <p class="text-sm mb-1"><strong>Average Percentage:</strong> {{ number_format($reportCard->student->results->where('term_id', $term->id ?? null)->avg('total') ?? 0, 1) }}%</p>
            @if($reportCard->student->relationLoaded('attendance') && $reportCard->student->attendance && $reportCard->student->attendance->count() > 0)
                @php
                    $attTerm = $reportCard->student->attendance->where('term_id', $term->id ?? null);
                @endphp
                @if($attTerm->count() > 0)
                <p class="text-sm mb-1"><strong>Attendance:</strong> {{ $attTerm->where('status', 'present')->count() }}/{{ $attTerm->count() }} days present</p>
                @endif
            @endif
        </div>

        <div class="mb-4">
            <div class="border p-3" style="border-color: #C9CDD3;">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Class Teacher's Remark</p>
                <p class="text-[12px] italic min-h-[2em]" style="color: #000000;">
                    {{ $reportCard->class_teacher_remark ?? '—' }}
                </p>
                <p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: #C9CDD3;">
                    Signature: ______________________ &nbsp; Date: ____________
                </p>
            </div>
        </div>

        <div class="mb-4">
            <div class="border p-3" style="border-color: #C9CDD3;">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Affective Domain</p>
                <p class="text-[12px] italic min-h-[2em]" style="color: #000000;">
                    {{ $reportCard->affective_domain ?? '—' }}
                </p>
            </div>
        </div>

        <div class="mb-4">
            <div class="border p-3" style="border-color: #C9CDD3;">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Psychomotor Assessment</p>
                <p class="text-[12px] italic min-h-[2em]" style="color: #000000;">
                    {{ $reportCard->psychomotor_assessment ?? '—' }}
                </p>
            </div>
        </div>

        <div class="mb-4">
            <div class="border p-3" style="border-color: #C9CDD3;">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Health Remarks</p>
                <p class="text-[12px] italic min-h-[2em]" style="color: #000000;">
                    {{ $reportCard->health_remarks ?? '—' }}
                </p>
            </div>
        </div>

        <div class="mb-4">
            <div class="border p-3" style="border-color: #C9CDD3;">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Principal's Remark</p>
                <p class="text-[12px] italic min-h-[2em]" style="color: #000000;">
                    {{ $reportCard->principal_remark ?? '—' }}
                </p>
                <p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: #C9CDD3;">
                    Signature: ______________________ &nbsp; Date: ____________
                </p>
            </div>
        </div>

        <div class="flex justify-center gap-3 text-[9px] text-gray-600 mb-4 flex-wrap">
            <span class="font-semibold uppercase tracking-wide" style="color: #059669;">Grading Key:</span>
            <span>A1 (75-100) Excellent</span><span>&middot;</span>
            <span>B2 (70-74) V.Good</span><span>&middot;</span>
            <span>B3 (65-69) Good</span><span>&middot;</span>
            <span>C4-C6 (50-64) Credit</span><span>&middot;</span>
            <span>D7 (45-49) Pass</span><span>&middot;</span>
            <span>E8 (40-44) Pass</span><span>&middot;</span>
            <span style="color: #A3312B;">F9 (0-39) Fail</span>
        </div>

        <div class="pt-3 mb-2">
            <hr style="border: 1px solid #C9CDD3;">
        </div>
        
        <p class="text-[10px] text-center" style="color: #6b7280;">
            Next Term Begins: <span class="font-semibold">{{ $reportCard->next_term_begins ? \Carbon\Carbon::parse($reportCard->next_term_begins)->format('d M Y') : 'TBA' }}</span>
        </p>
    </div>
</div>

</body>
</html>