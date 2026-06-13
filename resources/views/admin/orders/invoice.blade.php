<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111; }
        .row { display: flex; justify-content: space-between; gap: 24px; }
        .box { border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 13px; text-align: left; }
        th { background: #f6f6f6; }
        .text-right { text-align: right; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Print Invoice</button>
    <h2>Invoice</h2>
    <div class="row">
        <div>
            <strong>{{ $company['name'] }}</strong><br>
            GST: {{ $company['gst'] }}<br>
            {{ $company['address'] }}<br>
            {{ $company['phone'] }} | {{ $company['email'] }}
        </div>
        <div class="text-right">
            <strong>Order #{{ $order->order_number }}</strong><br>
            Date: {{ $order->created_at?->format('d M Y') }}<br>
            Payment: {{ strtoupper((string) $order->payment_method) }} / {{ ucfirst((string) $order->payment_status) }}
        </div>
    </div>
    <div class="box">
        <strong>Bill To:</strong><br>
        {{ $order->customer_name ?: ($order->shippingAddress->name ?? $order->user->name ?? 'N/A') }}<br>
        {{ $order->customer_phone ?: ($order->shippingAddress->phone ?? 'N/A') }}<br>
        {{ $order->shippingAddress?->fullAddress() ?: 'N/A' }}
    </div>

    <table>
        <thead><tr><th>Product</th><th>SKU</th><th>Weight</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->sku ?: optional($item->variant)->sku ?: 'N/A' }}</td>
                    <td>{{ $item->weight_kg ?: optional(optional($item->variant)->product)->weight_kg ?: 'N/A' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>₹{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>₹{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top: 20px;">
        <tr><td>Subtotal</td><td class="text-right">₹{{ number_format((float) $order->subtotal, 2) }}</td></tr>
        <tr><td>Shipping</td><td class="text-right">₹{{ number_format((float) $order->shipping, 2) }}</td></tr>
        <tr><td>Discount</td><td class="text-right">-₹{{ number_format((float) $order->discount, 2) }}</td></tr>
        <tr><td>Tax/GST</td><td class="text-right">₹{{ number_format((float) $order->tax_amount, 2) }}</td></tr>
        <tr><th>Grand Total</th><th class="text-right">₹{{ number_format((float) $order->total, 2) }}</th></tr>
    </table>
</body>
</html>
