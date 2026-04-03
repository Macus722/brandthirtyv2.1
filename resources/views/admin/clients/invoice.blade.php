<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $payment->invoice_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.6;
            background: #ffffff;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 50px 60px;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 30px;
            margin-bottom: 40px;
        }

        .brand {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .brand span {
            color: #dc2626;
        }

        .brand-sub {
            font-size: 10px;
            color: #94a3b8;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-size: 32px;
            font-weight: 300;
            color: #0f172a;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .invoice-title .invoice-num {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        /* ── Meta Info ── */
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .meta-block h4 {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #94a3b8;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .meta-block p {
            font-size: 13px;
            color: #334155;
            line-height: 1.8;
        }

        .meta-block .company-name {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        /* ── Table ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead th {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #94a3b8;
            font-weight: 600;
            text-align: left;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .items-table thead th:last-child {
            text-align: right;
        }

        .items-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .items-table tbody td:last-child {
            text-align: right;
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
        }

        .item-desc {
            font-weight: 500;
            color: #0f172a;
        }

        .item-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* ── Totals ── */
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .totals-table {
            width: 280px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: #64748b;
        }

        .totals-row.total {
            border-top: 2px solid #0f172a;
            margin-top: 8px;
            padding-top: 16px;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 30px;
            margin-top: 60px;
            text-align: center;
        }

        .footer p {
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.8;
        }

        .footer .thank-you {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .badge-paid {
            background: #dcfce7;
            color: #16a34a;
        }
    </style>
</head>

<body>
    <div class="invoice-container">

        {{-- Header --}}
        <div class="header">
            <div>
                <div class="brand">BRAND<span>THiRTY</span></div>
                <div class="brand-sub">Digital Marketing Agency</div>
            </div>
            <div class="invoice-title">
                <h1>Invoice</h1>
                <div class="invoice-num">#{{ $payment->invoice_number }}</div>
            </div>
        </div>

        {{-- Meta --}}
        <div class="meta-row">
            <div class="meta-block">
                <h4>Billed To</h4>
                <p class="company-name">{{ $payment->client->company_name }}</p>
                <p>
                    Attn: {{ $payment->client->pic_name }}<br>
                    {{ $payment->client->pic_phone }}<br>
                    @if($payment->client->pic_email){{ $payment->client->pic_email }}@endif
                </p>
            </div>
            <div class="meta-block" style="text-align: right;">
                <h4>Invoice Details</h4>
                <p>
                    <strong>Date:</strong> {{ $payment->paid_at->format('d F Y') }}<br>
                    <strong>Period:</strong>
                    {{ \Carbon\Carbon::create($payment->period_year, $payment->period_month)->format('F Y') }}<br>
                    <strong>Status:</strong> <span class="badge badge-paid">Paid</span>
                </p>
            </div>
        </div>

        {{-- Items --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Description</th>
                    <th>Period</th>
                    <th>Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="item-desc">
                            {{ $payment->client->isRecurring() ? 'Monthly Retainer Fee' : 'Contract Installment Payment' }}
                        </div>
                        <div class="item-sub">
                            {{ $payment->client->isRecurring() ? 'Recurring Retainer' : 'Fixed Contract' }}
                            — {{ $payment->client->company_name }}
                        </div>
                        @if($payment->notes)
                            <div class="item-sub" style="margin-top: 6px;">Note: {{ $payment->notes }}</div>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::create($payment->period_year, $payment->period_month)->format('M Y') }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="totals-table">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>RM {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>Tax (0%)</span>
                    <span>RM 0.00</span>
                </div>
                <div class="totals-row total">
                    <span>Total</span>
                    <span>RM {{ number_format($payment->amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p class="thank-you">Thank you for your business.</p>
            <p>
                BrandThirty Sdn. Bhd.<br>
                This is a computer-generated document. No signature is required.
            </p>
        </div>
    </div>
</body>

</html>