<!DOCTYPE html>
<html>

<head>
    <title>Order Status Update</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Order Status Update</h2>
    <p>Dear {{ $order->customer_name }},</p>
    <p>Unfortunately, we cannot proceed with your order <strong>#{{ $order->order_id }}</strong> at this time.</p>
    <p><strong>Reason:</strong></p>
    <blockquote style="background: #f9f9f9; border-left: 5px solid #ccc; margin: 1.5em 10px; padding: 0.5em 10px;">
        {{ $order->rejection_reason }}
    </blockquote>
    <p>Please address the issue mentioned above or contact our support team for assistance.</p>
    <p>Best Regards,<br>{{ $order->brand->name ?? 'BrandThirty' }} Team</p>
</body>

</html>