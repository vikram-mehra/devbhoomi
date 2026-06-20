@extends('emails.layouts.order')

@section('email_title', __('Order Status Updated - Order #:number', ['number' => $order->order_number]))
@section('email_header_subtitle', __('Order Status Update'))

@section('email_content')
    <p style="margin:0 0 8px;font-size:18px;font-weight:700;color:#0f172a;">{{ __('Hello :name,', ['name' => $order->customerDisplayName()]) }}</p>
    <p style="margin:0 0 24px;font-size:14px;line-height:1.65;color:#475569;">
        {{ __('The status of your order has been updated. Below are the details of the update:') }}
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px;">
        <tr>
            <td style="padding:18px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding:6px 0;font-size:13px;color:#64748b;width:42%;">{{ __('Order Number') }}</td>
                        <td style="padding:6px 0;font-size:14px;color:#0f172a;font-weight:700;">#{{ $order->order_number }}</td>
                    </tr>
                    @if(!empty($previousStatus))
                    <tr>
                        <td style="padding:6px 0;font-size:13px;color:#64748b;">{{ __('Previous Status') }}</td>
                        <td style="padding:6px 0;font-size:14px;color:#64748b;text-decoration:line-through;">{{ \App\Models\Order::statusLabel($previousStatus) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding:6px 0;font-size:13px;color:#64748b;">{{ __('New Status') }}</td>
                        <td style="padding:6px 0;font-size:14px;color:#0d9488;font-weight:700;">{{ \App\Models\Order::statusLabel($order->status) }}</td>
                    </tr>
                    @if($order->status === 'shipped' && $order->courier_name)
                    <tr>
                        <td style="padding:6px 0;font-size:13px;color:#64748b;">{{ __('Courier Partner') }}</td>
                        <td style="padding:6px 0;font-size:14px;color:#0f172a;">{{ $order->courier_name }}</td>
                    </tr>
                    @endif
                    @if($order->status === 'shipped' && $order->tracking_id)
                    <tr>
                        <td style="padding:6px 0;font-size:13px;color:#64748b;">{{ __('Tracking Number') }}</td>
                        <td style="padding:6px 0;font-size:14px;color:#0f172a;font-weight:700;">{{ $order->tracking_id }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding:6px 0;font-size:13px;color:#64748b;">{{ __('Updated On') }}</td>
                        <td style="padding:6px 0;font-size:14px;color:#0f172a;">{{ now()->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($order->status === 'shipped')
    <div style="background:#e0f2fe;border:1px solid #bae6fd;border-radius:12px;padding:16px 20px;margin-bottom:24px;">
        <div style="font-size:15px;font-weight:700;color:#0369a1;margin-bottom:6px;">🚀 {{ __('Your Order has Shipped!') }}</div>
        @if($order->courier_name)
            <div style="font-size:13px;color:#0369a1;margin-bottom:4px;"><strong>{{ __('Courier:') }}</strong> {{ $order->courier_name }}</div>
        @endif
        @if($order->tracking_id)
            <div style="font-size:13px;color:#0369a1;"><strong>{{ __('Tracking ID:') }}</strong> {{ $order->tracking_id }}</div>
        @endif
    </div>
    @endif

    <div style="text-align:center;margin:32px 0 24px;">
        <a href="{{ route('orders.show', $order) }}" style="display:inline-block;padding:12px 28px;background:#0d9488;color:#ffffff;text-decoration:none;border-radius:9999px;font-weight:700;font-size:14px;box-shadow:0 4px 12px rgba(13,148,136,0.15);">{{ __('View Order Details') }}</a>
    </div>

    <p style="margin:24px 0 0;font-size:13px;line-height:1.65;color:#64748b;text-align:center;">
        {{ __('Thank you for shopping with Devbhoomi Naturals!') }}
    </p>
@endsection
