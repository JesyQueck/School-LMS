<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-weight: normal;
            font-style: normal;
        }
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', 'Segoe UI', system-ui, sans-serif; color: #000; margin: 0; padding: 8px; font-size: 12px; width: 72mm; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #333; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 11px; }
        .label { color: #333; }
        .sub { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .school-name { font-size: 14px; font-weight: 700; color: #000; }
        .school-detail { font-size: 8px; color: #666; margin-top: 1px; }
        .receipt-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin: 6px 0; }
        .amount { font-size: 20px; font-weight: 700; color: #000; }
        .footer { font-size: 8px; color: #666; margin-top: 8px; text-align: center; }
        @media print { body { width: 100%; } }
    </style>
</head>
<body>
    <div class="center">
        <img src="{{ public_path('images/Logo.webp') }}" alt="{{ config('school.name') }}" style="height: 40px; width: auto; display: block; margin: 0 auto 2px;">
        <div class="school-name">{{ config('school.name', 'Greenfield Academy') }}</div>
        <div class="school-detail">{{ config('school.address', '123 Education Lane, Victoria Island, Lagos') }}</div>
        <div class="school-detail">{{ config('school.phone', '+234 800 000 0000') }} | {{ config('school.email', 'info@greenfieldacademy.edu') }}</div>
    </div>
    <div class="line"></div>

    <div class="center">
        <div class="receipt-title">Official Payment Receipt</div>
    </div>
    <div class="line"></div>

    <div class="row"><span class="label">Receipt No:</span><span>{{ $payment->receipt_number }}</span></div>
    <div class="row"><span class="label">Date:</span><span>{{ $payment->payment_date?->format('d M Y') }}</span></div>
    <div class="line"></div>

    <div class="row"><span class="label">Student:</span><span>{{ $payment->studentFee->student->full_name ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Admission No:</span><span>{{ $payment->studentFee->student->admission_no ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Class:</span><span>{{ $payment->studentFee->student->schoolClass->name ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Fee:</span><span>{{ $payment->studentFee->feeType->name ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Term:</span><span>{{ $payment->studentFee->term->name ?? 'N/A' }}</span></div>
    <div class="line"></div>

    <div class="center" style="margin: 8px 0;">
        <div class="label">Amount Paid</div>
        <div class="amount">₦{{ number_format($payment->amount_paid, 2) }}</div>
    </div>

    <div class="line"></div>
    <div class="row"><span class="label">Method:</span><span>{{ ucfirst($payment->payment_method ?? 'cash') }}</span></div>
    <div class="row"><span class="label">Reference:</span><span>{{ $payment->reference ?? '—' }}</span></div>
    <div class="row"><span class="label">Recorded By:</span><span>{{ $payment->recordedBy->name ?? 'System' }}</span></div>
    <div class="line"></div>

    <p class="footer">Thank you for your payment.<br>This is a computer generated receipt.</p>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>