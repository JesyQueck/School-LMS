<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; color: #1a1a1a; max-width: 480px; margin: 40px auto; padding: 0 20px; }
        .center { text-align: center; }
        .line { border-top: 1px solid #e5e5e5; margin: 16px 0; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .label { color: #666; }
        .sub { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 2px; }
        .amount { font-size: 28px; font-weight: 700; color: #047857; }
        .print-btn { margin: 24px auto; display: block; padding: 10px 24px; background: #047857; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="center">
        <img src="{{ public_path('images/Logo.webp') }}" alt="Greenfield Academy" style="height: 64px; width: auto; display: block; margin: 0 auto 4px;">
        <div class="sub">Official Payment Receipt</div>
    </div>

    <div class="line"></div>

    <div class="row"><span class="label">Receipt No:</span><span>{{ $payment->receipt_number }}</span></div>
    <div class="row"><span class="label">Date:</span><span>{{ $payment->payment_date?->format('d F Y') }}</span></div>
    <div class="line"></div>

    <div class="row"><span class="label">Student:</span><span>{{ $payment->studentFee->student->full_name ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Admission No:</span><span>{{ $payment->studentFee->student->admission_no ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Class:</span><span>{{ $payment->studentFee->student->schoolClass->name ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Fee:</span><span>{{ $payment->studentFee->feeType->name ?? 'N/A' }}</span></div>
    <div class="row"><span class="label">Term:</span><span>{{ $payment->studentFee->term->name ?? 'N/A' }}</span></div>
    <div class="line"></div>

    <div class="center" style="margin: 18px 0;">
        <div class="label">Amount Paid</div>
        <div class="amount">₦{{ number_format($payment->amount_paid, 2) }}</div>
    </div>

    <div class="row"><span class="label">Payment Method:</span><span>{{ ucfirst($payment->payment_method ?? 'cash') }}</span></div>
    <div class="row"><span class="label">Reference:</span><span>{{ $payment->reference ?? '—' }}</span></div>
    <div class="row"><span class="label">Recorded By:</span><span>{{ $payment->recordedBy->name ?? 'System' }}</span></div>
    <div class="line"></div>

    <p class="center" style="font-size: 12px; color: #888;">Thank you for your payment.</p>

    <button class="print-btn" onclick="window.print()">Print Receipt</button>
</body>
</html>
