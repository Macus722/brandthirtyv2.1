<!DOCTYPE html>
<html>

<head>
    <title>Order Completed</title>
</head>

<body style="font-family: 'Helvetica', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9f9f9;">
    <div style="max-width: 600px; margin: 30px auto; background: #ffffff; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
        <!-- Header -->
        <div style="background-color: #0a0a0c; padding: 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 1px;">Order Complete</h1>
            <p style="color: #10B981; margin: 8px 0 0; font-size: 13px; font-weight: bold;">YOUR PROJECT HAS BEEN DELIVERED</p>
        </div>

        <!-- Body -->
        <div style="padding: 30px;">
            <p>Dear {{ $order->customer_name }},</p>

            <p>We are pleased to inform you that your order <strong>#{{ $order->order_id }}</strong> has been
                completed and approved by our team.</p>

            <div style="background: #f0fdf4; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; font-weight: bold; color: #065f46;">Order Summary</p>
                <ul style="margin: 10px 0 0; padding-left: 20px; color: #333;">
                    <li><strong>Plan:</strong> {{ ucfirst($order->plan) }}</li>
                    <li><strong>Total:</strong> RM {{ number_format($order->total_amount, 2) }}</li>
                    <li><strong>Completed:</strong> {{ $order->completed_at ? $order->completed_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</li>
                </ul>
            </div>

            @if(!empty($order->report_file))
                <p>Your final report has been <strong>attached to this email</strong>. Please download and review
                    the deliverables at your convenience.</p>
            @else
                <p>No report file was attached to this order. If you were expecting deliverables, please
                    contact our support team.</p>
            @endif

            <p>Thank you for choosing <strong>{{ $order->brand->name ?? 'BrandThirty' }}</strong>. We look forward to working with you again.</p>

            <p style="margin-top: 30px;">Best Regards,<br>
                <strong>{{ $order->brand->name ?? 'BrandThirty' }} Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f8f8; padding: 20px; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee;">
            &copy; 2026 BrandThirty Media Authority. All rights reserved.
        </div>
    </div>
</body>

</html>
