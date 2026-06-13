<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Email verification code') }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(15,23,42,0.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);padding:32px 28px;text-align:center;">
                        <div style="font-size:24px;font-weight:700;color:#ffffff;">{{ config('app.name') }}</div>
                        <div style="font-size:13px;color:rgba(255,255,255,0.92);margin-top:8px;">{{ __('Secure email verification') }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 28px;text-align:center;">
                        <p style="margin:0 0 8px;font-size:17px;color:#0f172a;font-weight:600;">{{ __('Hello :name,', ['name' => $user->name]) }}</p>
                        <p style="margin:0 0 28px;font-size:14px;line-height:1.65;color:#475569;">
                            {{ __('Thank you for signing up. Use the one-time verification code below to activate your account:') }}
                        </p>
                        <table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:0 auto 24px;">
                            <tr>
                                <td style="padding:20px 32px;border-radius:12px;background:#f0fdfa;border:2px dashed #14b8a6;">
                                    <span style="font-size:36px;font-weight:800;letter-spacing:0.28em;color:#0f766e;font-family:ui-monospace,'Courier New',monospace;">{{ $code }}</span>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:0 0 12px;font-size:14px;color:#334155;font-weight:600;">
                            ⏱ {{ __('This code expires in :minutes minutes.', ['minutes' => $expireMinutes]) }}
                        </p>
                        <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
                            {{ __('Do not share this code. If you did not create an account, ignore this email.') }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 28px 28px;border-top:1px solid #e2e8f0;background:#f8fafc;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#64748b;line-height:1.5;">
                            {{ __('Need help?') }}
                            <a href="mailto:{{ config('verification.support_email') }}" style="color:#0d9488;text-decoration:none;font-weight:600;">{{ config('verification.support_email') }}</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
