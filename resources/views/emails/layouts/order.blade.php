<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('email_title', config('app.name'))</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(15,23,42,0.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);padding:28px 32px;text-align:center;">
                        @if(!empty($logoUrl))
                            <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" width="160" style="max-width:160px;height:auto;display:block;margin:0 auto 12px;border:0;">
                        @endif
                        <div style="font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">{{ config('app.name') }}</div>
                        @hasSection('email_header_subtitle')
                            <div style="font-size:13px;color:rgba(255,255,255,0.92);margin-top:8px;">@yield('email_header_subtitle')</div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 28px 24px;">
                        @yield('email_content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 28px 28px;border-top:1px solid #e2e8f0;background:#f8fafc;text-align:center;">
                        <p style="margin:0 0 6px;font-size:12px;color:#64748b;line-height:1.5;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                        </p>
                        <p style="margin:0;font-size:12px;color:#64748b;line-height:1.5;">
                            {{ __('Need help?') }}
                            <a href="mailto:{{ config('mail.from.address') }}" style="color:#0d9488;text-decoration:none;font-weight:600;">{{ config('mail.from.address') }}</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
