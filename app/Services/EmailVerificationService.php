<?php

namespace App\Services;

use App\Mail\VerifyEmailMail;
use App\Models\User;
use App\Support\MailConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Manual signup: 6-digit OTP on users.verification_code (hashed) + verification_expires_at.
 * Google signup: skipped — email_verified_at set in GoogleAuthService.
 */
class EmailVerificationService
{
    public function requiresVerification(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if (Str::endsWith((string) $user->email, '@otp.local')) {
            return false;
        }

        // Google sign-in / sign-up: never require email OTP.
        if ($user->google_id) {
            return false;
        }

        return ! $user->hasVerifiedEmail();
    }

    public function isSmtpConfigured(): bool
    {
        return MailConfig::isSmtpReady();
    }

    /**
     * Manual signup only: generate OTP, store on user, send SMTP email.
     *
     * @throws \RuntimeException
     */
    public function sendVerificationEmail(User $user): void
    {
        if ($user->google_id) {
            return;
        }

        $plainCode = $this->createOtp($user);

        if (! $this->isSmtpConfigured()) {
            if ($this->useLocalCodeFallback()) {
                $this->rememberLocalDevCode($user->email, $plainCode);
                $this->rememberResendCooldown($user->email);
                Log::info('Email OTP (local — SMTP not configured)', ['user_id' => $user->id]);

                return;
            }

            throw new \RuntimeException(
                __('Email is not configured. Set MAIL_PASSWORD in .env or run: php artisan mail:set-password')
            );
        }

        try {
            Mail::mailer('smtp')->to($user->email)->send(new VerifyEmailMail($user, $plainCode));
        } catch (\Throwable $e) {
            Log::error('Verification email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            if ($this->useLocalCodeFallback()) {
                $this->rememberLocalDevCode($user->email, $plainCode);
                $this->rememberResendCooldown($user->email);

                return;
            }

            throw new \RuntimeException(
                __('Could not send verification email. Check Gmail SMTP settings and try again.'),
                0,
                $e
            );
        }

        $this->rememberResendCooldown($user->email);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function verifyCode(User $user, string $code): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new \InvalidArgumentException(__('This email address is already verified.'));
        }

        if ($user->google_id) {
            $user->markEmailAsVerified();

            return;
        }

        $code = preg_replace('/\D/', '', $code);
        $length = (int) config('verification.code_length', 6);

        if (strlen($code) !== $length) {
            throw new \InvalidArgumentException(__('Please enter the :length-digit verification code.', ['length' => $length]));
        }

        if (! $user->verification_code || ! $user->verification_expires_at) {
            throw new \InvalidArgumentException(__('Invalid or expired verification code. Please request a new one.'));
        }

        if ($user->verification_expires_at->isPast()) {
            $user->clearEmailVerificationCode();
            throw new \InvalidArgumentException(__('This verification code has expired. Please request a new one.'));
        }

        $maxAttempts = (int) config('verification.max_attempts', 5);
        $attempts = $this->verificationAttempts($user);

        if ($attempts >= $maxAttempts) {
            throw new \InvalidArgumentException(__('Too many failed attempts. Please request a new verification code.'));
        }

        if (! hash_equals((string) $user->verification_code, $this->hashOtp($code))) {
            $this->incrementVerificationAttempts($user);
            $remaining = max(0, $maxAttempts - $this->verificationAttempts($user));

            throw new \InvalidArgumentException(
                $remaining > 0
                    ? __('Invalid verification code. :remaining attempt(s) remaining.', ['remaining' => $remaining])
                    : __('Invalid verification code. Please request a new code.')
            );
        }

        $user->markEmailAsVerified();
        $this->forgetVerificationAttempts($user);
        $this->forgetLocalDevCode($user->email);
    }

    public function getLocalDevCode(string $email): ?string
    {
        if (! $this->useLocalCodeFallback() || $this->isSmtpConfigured()) {
            return null;
        }

        $code = Cache::get($this->localDevCodeCacheKey($email));

        return is_string($code) && $code !== '' ? $code : null;
    }

    public function ensureLocalDevCodeForDisplay(User $user): ?string
    {
        if ($user->google_id || ! $this->useLocalCodeFallback() || $this->isSmtpConfigured()) {
            return null;
        }

        if ($cached = $this->getLocalDevCode($user->email)) {
            if ($this->hasActiveCode($user)) {
                return $cached;
            }
        }

        $plainCode = $this->createOtp($user);
        $this->rememberLocalDevCode($user->email, $plainCode);

        return $plainCode;
    }

    public function resendCooldownRemaining(string $email): int
    {
        $expiresAt = Cache::get($this->resendCacheKey($email));

        if (! $expiresAt) {
            return 0;
        }

        return max(0, (int) $expiresAt - time());
    }

    public function canResend(string $email): bool
    {
        return $this->resendCooldownRemaining($email) === 0;
    }

    public function findUnverifiedByEmail(string $email): ?User
    {
        $email = strtolower(trim($email));

        if ($email === '') {
            return null;
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user || ! $this->requiresVerification($user)) {
            return null;
        }

        return $user;
    }

    public function otpExpiresInSeconds(User $user): int
    {
        if (! $this->hasActiveCode($user)) {
            return 0;
        }

        return max(0, $user->verification_expires_at->getTimestamp() - time());
    }

    protected function createOtp(User $user): string
    {
        $length = (int) config('verification.code_length', 6);
        $max = (int) str_repeat('9', $length);
        $plainCode = str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);

        $minutes = max(1, (int) config('verification.token_expire_minutes', 10));

        $user->forceFill([
            'verification_code' => $this->hashOtp($plainCode),
            'verification_expires_at' => now()->addMinutes($minutes),
            'account_status' => User::ACCOUNT_INACTIVE,
            'email_verified_at' => null,
        ])->save();

        $this->forgetVerificationAttempts($user);

        return $plainCode;
    }

    protected function hasActiveCode(User $user): bool
    {
        return $user->verification_code
            && $user->verification_expires_at
            && ! $user->verification_expires_at->isPast();
    }

    protected function hashOtp(string $code): string
    {
        return hash('sha256', preg_replace('/\D/', '', $code));
    }

    protected function verificationAttempts(User $user): int
    {
        return (int) Cache::get($this->attemptsCacheKey($user), 0);
    }

    protected function incrementVerificationAttempts(User $user): void
    {
        $key = $this->attemptsCacheKey($user);
        $attempts = $this->verificationAttempts($user) + 1;
        $ttl = max(60, (int) config('verification.token_expire_minutes', 10) * 60);
        Cache::put($key, $attempts, $ttl);
    }

    protected function forgetVerificationAttempts(User $user): void
    {
        Cache::forget($this->attemptsCacheKey($user));
    }

    protected function attemptsCacheKey(User $user): string
    {
        return 'email_verify_attempts:'.$user->id;
    }

    protected function useLocalCodeFallback(): bool
    {
        return app()->environment('local')
            && config('verification.local_code_fallback', true);
    }

    protected function rememberResendCooldown(string $email): void
    {
        $seconds = max(1, (int) config('verification.resend_cooldown_seconds', 60));
        Cache::put($this->resendCacheKey($email), time() + $seconds, $seconds);
    }

    protected function resendCacheKey(string $email): string
    {
        return 'email_verify_resend:'.sha1(strtolower(trim($email)));
    }

    protected function rememberLocalDevCode(string $email, string $code): void
    {
        if (! $this->useLocalCodeFallback()) {
            return;
        }

        $minutes = max(1, (int) config('verification.token_expire_minutes', 10));
        Cache::put($this->localDevCodeCacheKey($email), $code, now()->addMinutes($minutes));
        session()->flash('dev_verification_code', $code);
    }

    protected function forgetLocalDevCode(string $email): void
    {
        Cache::forget($this->localDevCodeCacheKey($email));
    }

    protected function localDevCodeCacheKey(string $email): string
    {
        return 'email_verify_dev_code:'.sha1(strtolower(trim($email)));
    }
}
