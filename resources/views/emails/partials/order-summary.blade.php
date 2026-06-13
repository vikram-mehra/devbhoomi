@php
    $order = $order ?? null;
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:0 0 20px;">
    <thead>
        <tr>
            <th align="left" style="padding:10px 12px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">{{ __('Product') }}</th>
            <th align="center" style="padding:10px 12px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">{{ __('Qty') }}</th>
            <th align="right" style="padding:10px 12px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">{{ __('Price') }}</th>
            <th align="right" style="padding:10px 12px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">{{ __('Total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
            <tr>
                <td style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;vertical-align:top;">
                    <strong style="display:block;margin-bottom:4px;">{{ $item->product_name }}</strong>
                    @if($item->variant_label)
                        <span style="font-size:12px;color:#64748b;">{{ $item->variant_label }}</span>
                    @endif
                </td>
                <td align="center" style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#334155;">{{ $item->qty }}</td>
                <td align="right" style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#334155;white-space:nowrap;">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                <td align="right" style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;font-weight:600;white-space:nowrap;">₹{{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:8px;">
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#475569;">{{ __('Subtotal') }}</td>
        <td align="right" style="padding:6px 0;font-size:14px;color:#0f172a;font-weight:600;">₹{{ number_format((float) $order->subtotal, 2) }}</td>
    </tr>
    @if((float) $order->discount > 0)
        <tr>
            <td style="padding:6px 0;font-size:14px;color:#475569;">{{ __('Discount') }}</td>
            <td align="right" style="padding:6px 0;font-size:14px;color:#16a34a;font-weight:600;">− ₹{{ number_format((float) $order->discount, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#475569;">{{ __('GST / Tax') }}</td>
        <td align="right" style="padding:6px 0;font-size:14px;color:#0f172a;font-weight:600;">₹{{ number_format((float) $order->tax_amount, 2) }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#475569;">{{ __('Shipping Charges') }}</td>
        <td align="right" style="padding:6px 0;font-size:14px;color:#0f172a;font-weight:600;">₹{{ number_format((float) $order->shipping, 2) }}</td>
    </tr>
    @if((float) $order->wallet_used > 0)
        <tr>
            <td style="padding:6px 0;font-size:14px;color:#475569;">{{ __('Wallet Used') }}</td>
            <td align="right" style="padding:6px 0;font-size:14px;color:#16a34a;font-weight:600;">− ₹{{ number_format((float) $order->wallet_used, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td style="padding:14px 0 0;font-size:16px;color:#0f172a;font-weight:700;border-top:2px solid #e2e8f0;">{{ __('Grand Total') }}</td>
        <td align="right" style="padding:14px 0 0;font-size:18px;color:#0d9488;font-weight:800;border-top:2px solid #e2e8f0;white-space:nowrap;">₹{{ number_format((float) $order->total, 2) }}</td>
    </tr>
</table>
