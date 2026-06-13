<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2 style="margin-bottom: 8px;">Order Status Updated</h2>
    <p style="margin-top: 0;">Hello {{ $order->customer_name ?: ($order->user->name ?? 'Customer') }},</p>
    <p>Your order <strong>{{ $order->order_number }}</strong> status has been updated.</p>
    <p>
        Previous status:
        <strong>{{ $previousStatus ? \App\Models\Order::statusLabel($previousStatus) : 'N/A' }}</strong><br>
        Current status:
        <strong>{{ \App\Models\Order::statusLabel($order->status) }}</strong>
    </p>
    <p>Thank you for shopping with us.</p>
</body>
</html>
