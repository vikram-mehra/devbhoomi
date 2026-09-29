@extends('emails.layouts.order')

@section('email_title', __('Order Confirmation - Order #:number', ['number' => $order->order_number]))
@section('email_header_subtitle', __('Order Confirmation'))

@section('email_content')
    <p style="margin:0 0 8px;font-size:18px;font-weight:700;color:#0f172a;">{{ __('Hello :name,', ['name' => $order->customerDisplayName()]) }}</p>
    <p style="margin:0 0 24px;font-size:14px;line-height:1.65;color:#475569;">
        {{ __('Thank you for your order! We have received your order and will begin processing it shortly.') }}
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px;">
        <tr>
            <td style="padding:18px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#64748b;width:42%;">{{ __('Order Number') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#0f172a;font-weight:700;">#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#64748b;">{{ __('Order Date') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#0f172a;">{{ $order->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#64748b;">{{ __('Payment Method') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#0f172a;">{{ $order->paymentMethodLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#64748b;">{{ __('Order Status') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#0d9488;font-weight:700;">{{ \App\Models\Order::statusLabel($order->status) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#64748b;">{{ __('Payment Status') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#0f172a;">{{ $order->paymentStatusLabel() }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:24px;">
        <tr>
            <td valign="top" width="48%" style="padding-right:8px;">
                <div style="font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:10px;">{{ __('Billing Address') }}</div>
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;font-size:13px;line-height:1.6;color:#334155;">
                    @foreach($order->billingAddressLines() as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                </div>
            </td>
            <td valign="top" width="48%" style="padding-left:8px;">
                <div style="font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:10px;">{{ __('Shipping Address') }}</div>
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;font-size:13px;line-height:1.6;color:#334155;">
                    @foreach($order->shippingAddressLines() as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:12px;">{{ __('Order Items') }}</div>
    @include('emails.partials.order-summary', ['order' => $order])

    <p style="margin:24px 0 0;font-size:13px;line-height:1.65;color:#64748b;text-align:center;">
        {{ __('You can track your order anytime from your account dashboard.') }}
    </p>
@endsection
