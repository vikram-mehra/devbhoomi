@extends('emails.layouts.order')

@section('email_title', __('New Order Received - Order #:number', ['number' => $order->order_number]))
@section('email_header_subtitle', __('New Order Notification'))

@section('email_content')
    <p style="margin:0 0 8px;font-size:18px;font-weight:700;color:#0f172a;">{{ __('New order received') }}</p>
    <p style="margin:0 0 24px;font-size:14px;line-height:1.65;color:#475569;">
        {{ __('A customer has successfully placed a new order on your store.') }}
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;margin-bottom:24px;">
        <tr>
            <td style="padding:18px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#9a3412;width:42%;">{{ __('Order Number') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#7c2d12;font-weight:700;">#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#9a3412;">{{ __('Order Time') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#7c2d12;">{{ $order->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#9a3412;">{{ __('Total Amount') }}</td>
                        <td style="padding:4px 0;font-size:16px;color:#c2410c;font-weight:800;">₹{{ number_format((float) $order->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#9a3412;">{{ __('Payment Method') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#7c2d12;">{{ $order->paymentMethodLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;font-size:13px;color:#9a3412;">{{ __('Payment Status') }}</td>
                        <td style="padding:4px 0;font-size:14px;color:#7c2d12;font-weight:700;">{{ $order->paymentStatusLabel() }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:12px;">{{ __('Customer Details') }}</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px;">
        <tr>
            <td style="padding:18px 20px;font-size:13px;line-height:1.8;color:#334155;">
                <div><strong>{{ __('Name') }}:</strong> {{ $order->customerDisplayName() }}</div>
                <div><strong>{{ __('Email') }}:</strong> {{ $order->customerDisplayEmail() ?: '—' }}</div>
                <div><strong>{{ __('Phone') }}:</strong> {{ $order->customerDisplayPhone() }}</div>
            </td>
        </tr>
    </table>

    <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:12px;">{{ __('Complete Order Summary') }}</div>
    @include('emails.partials.order-summary', ['order' => $order])

    <div style="font-size:15px;font-weight:700;color:#0f172a;margin:24px 0 12px;">{{ __('Shipping Address') }}</div>
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;font-size:13px;line-height:1.6;color:#334155;margin-bottom:8px;">
        @foreach($order->shippingAddressLines() as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>
@endsection
