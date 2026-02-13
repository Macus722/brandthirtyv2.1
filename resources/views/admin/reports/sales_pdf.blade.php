<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Report</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #E53E3E;
            font-size: 24px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            color: #777;
            font-size: 12px;
        }

        .stats-start {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .stat-box {
            width: 32%;
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #eee;
            display: inline-block;
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #777;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }

        th {
            background: #E53E3E;
            color: #fff;
            padding: 10px;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-green {
            background: #C6F6D5;
            color: #22543D;
        }

        .badge-red {
            background: #FED7D7;
            color: #822727;
        }

        .badge-yellow {
            background: #FEFCBF;
            color: #744210;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>BrandThirty</h1>
        <p>Sales Report Generated on {{ date('d M Y, h:i A') }}</p>
    </div>

    <!-- Stats Summary (Using inline-block for simple PDF grid) -->
    <div style="background: #f8f8f8; padding: 15px; margin-bottom: 30px; border: 1px solid #eee;">
        <table style="width: 100%; border: none; margin: 0;">
            <tr style="background: transparent;">
                <td style="text-align: center; width: 33%; border: none;">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">RM {{ number_format((float) $totalRevenue, 2) }}</div>
                </td>
                <td style="text-align: center; width: 33%; border: none;">
                    <div class="stat-label">Avg Order Value</div>
                    <div class="stat-value">RM {{ number_format((float) $aov, 2) }}</div>
                </td>
                <td style="text-align: center; width: 33%; border: none;">
                    <div class="stat-label">Potential Sales</div>
                    <div class="stat-value">RM {{ number_format((float) $potentialRevenue, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h3>Transaction Detail</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Service Plan</th>
                <th class="text-right">Amount (RM)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                    <td>#{{ $order->order_id }}</td>
                    <td>
                        {{ $order->customer_name }}<br>
                        <span style="color: #999; font-size: 10px;">{{ $order->company_name }}</span>
                    </td>
                    <td>{{ $order->plan }}</td>
                    <td class="text-right">{{ number_format((float) $order->total_amount, 2) }}</td>
                    <td>
                        @if(in_array($order->status, ['Paid', 'Completed']))
                            <span class="badge badge-green">PAID</span>
                        @elseif(in_array($order->status, ['Processing', 'In Progress', 'Approved']))
                            <span class="badge badge-green">SECURED</span>
                        @elseif($order->status == 'Rejected')
                            <span class="badge badge-red">REJECTED</span>
                        @else
                            <span class="badge badge-yellow">PENDING</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right" style="font-weight: bold; text-transform: uppercase;">Total</td>
                <td class="text-right" style="font-weight: bold; color: #E53E3E;">RM
                    {{ number_format((float) $potentialRevenue, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} BrandThirty. Confidential Report.
    </div>
</body>

</html>