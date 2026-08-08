<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Card - {{ $reportCard->student->full_name ?? $reportCard->student->admission_no }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --green: #059669;
        --navy: #16324F;
        --navy-dim: #2C4B6E;
        --gold: #B8860B;
        --paper: #FDFCF8;
        --ink: #1F2A33;
        --line: #C9CDD3;
        --pass: #1B6B3A;
        --fail: #A3312B;
    }

    * { box-sizing: border-box; }

    body {
        background: #e9e9e4;
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        color: #000000;
        margin: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .font-display { font-family: 'Source Serif 4', Georgia, serif; }

    @page { size: A4; margin: 0; }

    .sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 10mm auto;
        background: var(--paper);
        position: relative;
        box-shadow: 0 4px 24px rgba(0,0,0,0.15);
    }

    @media print {
        body { background: none; }
        .sheet { margin: 0; box-shadow: none; }
    }

    .frame {
        position: absolute;
        inset: 8mm;
        border: 2px solid var(--navy);
        pointer-events: none;
    }

    .frame::before {
        content: "";
        position: absolute;
        inset: 4px;
        border: 1px solid var(--gold);
    }

    .corner {
        position: absolute;
        width: 10mm;
        height: 10mm;
        border: 2px solid var(--gold);
    }

    .corner-tl { top: -1px; left: -1px; border-right: none; border-bottom: none; }
    .corner-tr { top: -1px; right: -1px; border-left: none; border-bottom: none; }
    .corner-bl { bottom: -1px; left: -1px; border-right: none; border-top: none; }
    .corner-br { bottom: -1px; right: -1px; border-left: none; border-top: none; }

    .content { position: relative; padding: 16mm 15mm; }

    table.results th {
        background: var(--green);
        color: white;
        font-weight: 600;
        letter-spacing: 0.04em;
    }

    table.results tbody tr:nth-child(even) { background: #F4F2EA; }

    table.results td, table.results th {
        border: 1px solid var(--line);
        color: #000000;
    }

    .badge {
        border: 1px solid currentColor;
        padding: 2px 10px;
        font-size: 10px;
        letter-spacing: 0.08em;
        font-weight: 700;
    }
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
        <div class="flex items-center justify-between gap-4 pb-4 border-b-2" style="border-color: var(--navy);">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style="border: 2px solid var(--navy);">
                    <span class="font-display text-xs font-bold text-center leading-tight" style="color: var(--green);">{{ substr($school->name ?? 'GHS', 0, 3) }}</span>
                </div>
                <div>
                    <h1 class="font-display text-lg font-bold tracking-wide" style="color: var(--navy);">{{ $school->name ?? 'School Name' }}</h1>
                    <p class="text-[11px] text-gray-600 mt-0.5">
                        {{ $school->address ?? 'School Address, City, State' }}<br>
                        {{ $school->email ?? '' }} | {{ $school->phone ?? '' }}
                    </p>
                </div>
            </div>
            
            <div class="text-right">
                <span class="badge" style="color: {{ ($reportCard->is_published ?? false) ? 'var(--pass)' : 'var(--fail)' }};">
                    {{ ($reportCard->is_published ?? false) ? 'PUBLISHED' : strtoupper($reportCard->status ?? 'DRAFT') }}
                </span>
            </div>
        </div>

        <div class="text-center mt-4 mb-5">
            <p class="uppercase text-sm font-bold tracking-[0.25em]" style="color: var(--green);">
                Terminal Report Sheet
            </p>
            <p class="text-xs text-gray-600 mt-1">
                {{ $term->name ?? 'Term' }} @if(!empty($term->session))&middot; {{ $term->session }} @endif Session
            </p>
        </div>

        <div class="grid grid-cols-2 gap-x-10 gap-y-2 text-[13px] mb-6">
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
                    <th class="text-left py-2 px-3 uppercase text-[10px]">Subject</th>
                    <th class="py-2 px-2 uppercase text-[10px] w-16">CA</th>
                    <th class="py-2 px-2 uppercase text-[10px] w-16">Exam</th>
                    <th class="py-2 px-2 uppercase text-[10px] w-16">Total</th>
                    <th class="py-2 px-2 uppercase text-[10px] w-16">Grade</th>
                    <th class="text-left py-2 px-3 uppercase text-[10px]">Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportCard->student->results as $result)
                    <tr>
                        <td class="py-1.5 px-3">{{ $result->classSubject->subject->name ?? '—' }}</td>
                        <td class="py-1.5 px-2 text-center">{{ $result->ca_score ?? 'N/A' }}</td>
                        <td class="py-1.5 px-2 text-center">{{ $result->exam_score ?? 'N/A' }}</td>
                        <td class="py-1.5 px-2 text-center font-semibold">{{ $result->total ?? 'N/A' }}</td>
                        <td class="py-1.5 px-2 text-center font-bold"
                            style="color: {{ $result->grade === 'F9' ? 'var(--fail)' : 'var(--navy)' }};">
                            {{ $result->grade ?? 'N/A' }}
                        </td>
                        <td class="py-1.5 px-3">{{ $result->remark ?? 'N/A' }}</td>
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
            @if(isset($reportCard->student->attendance) && $reportCard->student->attendance->count() > 0)
            <p class="text-sm mb-1"><strong>Attendance:</strong> {{ $reportCard->student->attendance->where('term_id', $term->id ?? null)->where('status', 'present')->count() }}/{{ $reportCard->student->attendance->where('term_id', $term->id ?? null)->count() }} days present</p>
            @endif
        </div>

        <div class="mb-4">
            <div class="border p-3" style="border-color: var(--line);">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Class Teacher's Remark</p>
                <p class="text-[12px] italic min-h-[2em]">
                    {{ $reportCard->class_teacher_remark ?? '—' }}
                </p>
                <p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: var(--line);">
                    Signature: ______________________ &nbsp; Date: ____________
                </p>
            </div>
        </div>

        <div class="mb-4">
            <div class="border p-3" style="border-color: var(--line);">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Affective Domain</p>
                <p class="text-[12px] italic min-h-[2em]">
                    {{ $reportCard->affective_domain ?? '—' }}
                </p>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="border p-3" style="border-color: var(--line);">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Psychomotor Assessment</p>
                <p class="text-[12px] italic min-h-[2em]">
                    {{ $reportCard->psychomotor_assessment ?? '—' }}
                </p>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="border p-3" style="border-color: var(--line);">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Health Remarks</p>
                <p class="text-[12px] italic min-h-[2em]">
                    {{ $reportCard->health_remarks ?? '—' }}
                </p>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="border p-3" style="border-color: var(--line);">
                <p class="uppercase text-[10px] tracking-wide text-gray-500 mb-1">Principal's Remark</p>
                <p class="text-[12px] italic min-h-[2em]">
                    {{ $reportCard->principal_remark ?? '—' }}
                </p>
                <p class="text-[10px] text-gray-500 mt-4 pt-2 border-t" style="border-color: var(--line);">
                    Signature: ______________________ &nbsp; Date: ____________
                </p>
            </div>
        </div>

        <div class="flex justify-center gap-3 text-[9px] text-gray-600 mb-4 flex-wrap">
            <span class="font-semibold uppercase tracking-wide" style="color: var(--green);">Grading Key:</span>
            <span>A1 (75–100) Excellent</span><span>&middot;</span>
            <span>B2 (70–74) V.Good</span><span>&middot;</span>
            <span>B3 (65–69) Good</span><span>&middot;</span>
            <span>C4–C6 (50–64) Credit</span><span>&middot;</span>
            <span>D7 (45–49) Pass</span><span>&middot;</span>
            <span>E8 (40–44) Pass</span><span>&middot;</span>
            <span style="color: var(--fail);">F9 (0–39) Fail</span>
        </div>

        <div class="pt-3 mb-2">
            <hr style="border: 1px solid var(--line);">
        </div>
        
        <p class="text-[10px] text-center" style="color: #6b7280;">
            Next Term Begins: <span class="font-semibold">{{ $reportCard->next_term_begins ? \Carbon\Carbon::parse($reportCard->next_term_begins)->format('d M Y') : 'TBA' }}</span>
        </p>

    </div>
</div>

</body>
</html>