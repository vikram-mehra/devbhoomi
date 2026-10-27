<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reminderSubject }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f6f7;font-family:Arial,Helvetica,sans-serif;color:#252525;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f6f7;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px 18px;text-align:center;">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" style="display:block;max-width:180px;max-height:64px;width:auto;height:auto;margin:0 auto 16px;">
                            @else
                                <div style="font-size:22px;font-weight:700;color:#ec8951;margin-bottom:16px;">{{ config('app.name') }}</div>
                            @endif
                            <h1 style="font-size:24px;line-height:1.3;margin:0 0 12px;color:#222;">{{ $reminderSubject }}</h1>
                            <p style="font-size:15px;line-height:1.6;margin:0;color:#555;">
                                {{ __('Hi :name, your items are waiting for you whenever you are ready.', ['name' => $customer->name ?: __('there')]) }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                @foreach($items as $item)
                                    <tr>
                                        <td width="76" valign="top" style="padding:14px 12px 14px 0;border-bottom:1px solid #eeeeee;">
                                            @if($item['image_url'])
                                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" width="64" height="64" style="display:block;width:64px;height:64px;object-fit:cover;border-radius:6px;">
                                            @endif
                                        </td>
                                        <td valign="top" style="padding:14px 8px;border-bottom:1px solid #eeeeee;">
                                            <div style="font-size:15px;font-weight:600;line-height:1.4;color:#222;">{{ $item['name'] }}</div>
                                            @if($item['variant_label'] && $item['variant_label'] !== 'Default')
                                                <div style="font-size:13px;line-height:1.5;color:#777;">{{ $item['variant_label'] }}</div>
                                            @endif
                                            <div style="font-size:13px;line-height:1.5;color:#777;">{{ __('Qty: :qty', ['qty' => $item['qty']]) }}</div>
                                        </td>
                                        <td align="right" valign="top" style="padding:14px 0 14px 8px;border-bottom:1px solid #eeeeee;white-space:nowrap;font-size:14px;color:#333;">
                                            ₹{{ number_format($item['line_total'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">
                                <tr>
                                    <td style="font-size:15px;font-weight:700;color:#222;">{{ __('Cart subtotal') }}</td>
                                    <td align="right" style="font-size:15px;font-weight:700;color:#222;">₹{{ number_format($subtotal, 2) }}</td>
                                </tr>
                                @if($total !== null)
                                    <tr>
                                        <td style="padding-top:8px;font-size:14px;color:#555;">{{ __('Order total') }}</td>
                                        <td align="right" style="padding-top:8px;font-size:14px;color:#555;">₹{{ number_format($total, 2) }}</td>
                                    </tr>
                                @endif
                            </table>
                            <div style="text-align:center;padding:26px 0 10px;">
                                <a href="{{ $checkoutUrl }}" style="display:inline-block;background:#ec8951;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;padding:14px 26px;border-radius:7px;">
                                    {{ __('Complete Your Purchase') }}
                                </a>
                            </div>
                            <p style="font-size:14px;line-height:1.65;text-align:center;color:#555;margin:16px 0 0;">
                                {{ __('Need help or have any suggestions?') }}
                                @if($supportEmail !== '')
                                    <a href="mailto:{{ $supportEmail }}" style="color:#b8642e;">{{ __('Reply to this email or share your feedback') }}</a>
                                @else
                                    {{ __('Just reply to this email; we would love to hear from you.') }}
                                @endif
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background:#fafafa;text-align:center;font-size:12px;line-height:1.7;color:#777;">
                            <div>{{ __('Thank you for visiting our store.') }}</div>
                            <div>{{ __('Best regards, :store Team', ['store' => config('app.name')]) }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
