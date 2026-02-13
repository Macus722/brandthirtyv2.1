<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->order_id }}</title>
    <style>
        /* General Layout */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 40px;
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }

        .header-left {
            text-align: left;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            text-align: right;
            width: 50%;
            vertical-align: top;
        }

        .logo {
            height: 40px;
            width: auto;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #FF2D46;
            /* Brand Red */
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 2px;
        }

        .invoice-subtitle {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }

        /* Info Grid */
        .info-table {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }

        .info-col {
            width: 33.33%;
            vertical-align: top;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }

        .info-value p {
            margin: 0;
            font-weight: normal;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-top: 2px solid #FF2D46;
            /* Brand Red Accent */
        }

        .items-table th {
            text-align: left;
            padding: 12px 10px;
            background-color: #f8f8f8;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
            text-transform: uppercase;
            color: #555;
            font-weight: bold;
        }

        .items-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .items-table tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .text-right {
            text-align: right;
        }

        /* Totals */
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .totals-table td {
            padding: 8px 10px;
            text-align: right;
        }

        .total-label {
            font-weight: bold;
            color: #777;
        }

        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #FF2D46;
        }

        /* Footer */
        .footer {
            margin-top: 80px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .footer-company {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }

        .badge-paid {
            background-color: #10B981;
            /* Green */
        }

        .badge-pending {
            background-color: #F59E0B;
            /* Yellow */
        }

        .badge-rejected {
            background-color: #EF4444;
            /* Red */
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td class="header-left">
                <!-- Dynamic Logo -->
                @if($order->brand && $order->brand->logo)
                    <img src="{{ public_path($order->brand->logo) }}" class="logo" alt="{{ $order->brand->name }}">
                @else
                    <img src="{{ public_path('Images/B30_logo-04.png') }}" class="logo" alt="BrandThirty">
                @endif
            </td>
            <td class="header-right">
                <h1 class="invoice-title" style="color: {{ $order->brand->primary_color ?? '#FF2D46' }};">Invoice</h1>
                <div class="invoice-subtitle">Original Copy</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-col">
                <div class="info-label">Bill To</div>
                <div class="info-value">
                    {{ $order->customer_name }}<br>
                    <p>{{ $order->company_name ?? 'N/A' }}</p>
                    <p>{{ $order->customer_email }}</p>
                    <p>{{ $order->phone ?? '' }}</p>
                </div>
            </td>
            <td class="info-col">
                <div class="info-label">Invoice Details</div>
                <div class="info-value">
                    <p>Order ID: <strong>{{ $order->order_id }}</strong></p>
                    <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
                    <p>Status:
                        @if($order->status == 'Paid')
                            <span style="color: #10B981;">PAID</span>
                        @elseif($order->status == 'Rejected')
                            <span style="color: #EF4444;">CANCELLED</span>
                        @else
                            <span style="color: #F59E0B;">PENDING</span>
                        @endif
                    </p>
                </div>
            </td>
            <td class="info-col text-right">
                <div class="info-label">Issued By</div>
                <div class="info-value">
                    {{ $order->brand->name ?? 'BrandThirty' }}<br>
                    <p>admin@{{ strtolower($order->brand->name ?? 'brandthirty') }}.com</p>
                    <p>www.{{ strtolower($order->brand->name ?? 'brandthirty') }}.com</p>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-right">Strategy</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="font-weight: bold;">{{ $order->plan }} Plan</div>
                    <div style="font-size: 11px; color: #777; margin-top: 4px;">Website URL: {{ $order->website_url }}
                    </div>
                </td>
                <td class="text-right">{{ $order->strategy }}</td>
                <td class="text-right">RM {{ number_format($order->total_amount, 2) }}</td>
            </tr>
            <!-- Placeholder for potential extra items or 'Service Fee' if needed -->
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="total-label">Subtotal:</td>
            <td>RM {{ number_format($order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="total-label">Tax (0%):</td>
            <td>RM 0.00</td>
        </tr>
        <tr>
            <td class="total-label" style="border-top: 1px solid #eee; padding-top: 10px;">Total Due:</td>
            <td class="total-amount" style="border-top: 1px solid #eee; padding-top: 10px;">RM
                {{ number_format($order->total_amount, 2) }}
            </td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div class="footer">
        <div class="footer-company">Thank you for choosing BrandThirty!</div>
        <div>If you have any questions about this invoice, please contact support.</div>
    </div>

</body>

</html>