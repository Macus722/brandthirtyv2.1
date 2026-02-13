<!DOCTYPE html>
<html>

<head>
    <title>Order Confirmation</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Order Confirmation</h2>
    <p>Dear {{ $order->customer_name }},</p>
    <p>We are pleased to inform you that your order <strong>#{{ $order->order_id }}</strong> has been approved and moved
        to the production phase.</p>
    <p>Please find the invoice attached to this email.</p>
    <p><strong>Order Details:</strong></p>
    <ul>
        <li>Plan: {{ $order->plan }}</li>
        <li>Total Amount: RM {{ number_format($order->total_amount, 2) }}</li>
        <li>Date: {{ $order->created_at->format('d M Y') }}</li>
    </ul>
    <p>If you have any questions, feel free to reply to this email.</p>
    <p>Best Regards,<br>{{ $order->brand->name ?? 'BrandThirty' }} Team</p>
</body>

</html>