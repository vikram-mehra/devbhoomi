# Email OTP Verification (Post-Signup)

Laravel 8+ compatible flow with hashed OTP storage, 10-minute expiry, resend cooldown, and brute-force protection.

## Flow

1. User registers → `EmailVerificationService::sendVerificationEmail()` creates OTP in `email_verification_otps`, sends mail.
2. User opens `/email/verification-sent?email=...` → enters 6-digit OTP.
3. Valid OTP → `email_verified_at` set, user logged in → home or vendor dashboard.
4. Invalid/expired → error with remaining attempts.

## Database

```bash
C:\xampp\php\php.exe artisan migrate
```

**Users table fields:** `email_verified_at`, `verification_code` (SHA-256 hash), `verification_expires_at` (10 min)

| Signup method | Behaviour |
|---------------|-----------|
| **Continue with Google** | `email_verified_at` set immediately, no OTP, login → home/vendor dashboard |
| **Manual form** | OTP emailed via SMTP, verify page, restricted until verified |

## Routes (`routes/web.php`)

| Method | URI | Name | Action |
|--------|-----|------|--------|
| GET | `/email/verification-sent` | `verification.sent` | OTP page |
| GET | `/email/verify` | `verification.notice` | Email lookup |
| POST | `/email/verify` | `verification.verify` | Submit OTP |
| POST | `/email/verify/resend` | `verification.resend` | Resend (JSON) |

Protected routes use middleware: `verified.email`

## `.env` — Gmail SMTP

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

VERIFICATION_TOKEN_EXPIRE=10
VERIFICATION_RESEND_COOLDOWN=60
VERIFICATION_MAX_ATTEMPTS=5
VERIFICATION_LOCAL_CODE_FALLBACK=true
```

App Password: https://myaccount.google.com/apppasswords

```bash
C:\xampp\php\php.exe artisan mail:set-password
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan mail:test-verification your@email.com
```

## Key files

- `app/Models/EmailVerificationOtp.php`
- `app/Services/EmailVerificationService.php`
- `app/Http/Controllers/Auth/EmailVerificationController.php`
- `app/Mail/VerifyEmailMail.php`
- `app/Http/Middleware/EnsureEmailIsVerified.php`
- `resources/views/auth/verify-otp.blade.php`
- `resources/views/emails/verify-email.blade.php`

## Security

- OTP stored as SHA-256 hash (never plain text in DB)
- Max 5 failed attempts per OTP (`VERIFICATION_MAX_ATTEMPTS`)
- Resend cooldown 60 seconds
- Old OTPs invalidated when a new one is issued
- Rate limits: `verification-verify`, `verification-resend`

## Local development

If `MAIL_PASSWORD` is empty and `APP_ENV=local`, OTP is shown on the verify page (not emailed). Set Gmail password for real inbox delivery.
