<?php

namespace App\Services;

use App\Exceptions\Msg91OtpException;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class Msg91OtpService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.msg91.enabled');
    }

    public function isConfigured(): bool
    {
        return trim((string) config('services.msg91.auth_key')) !== ''
            && trim((string) config('services.msg91.template_id')) !== '';
    }

    public function isDummy(): bool
    {
        if ((bool) config('services.msg91.dummy')) {
            return true;
        }

        return $this->isEnabled() && trim((string) config('services.msg91.template_id')) === '';
    }

    public function isAvailable(): bool
    {
        if ($this->isDummy()) {
            return true;
        }

        return $this->isEnabled() && $this->isConfigured();
    }

    public function dummyCode(): string
    {
        $code = preg_replace('/\D+/', '', (string) config('services.msg91.dummy_code', '123456')) ?? '';
        $length = $this->otpLength();
        if (strlen($code) === $length) {
            return $code;
        }

        return str_pad(substr(str_repeat('12345678', 2), 0, $length), $length, '0');
    }

    public function otpLength(): int
    {
        $length = (int) config('services.msg91.otp_length', 6);

        return $length >= 4 && $length <= 8 ? $length : 6;
    }

    public function otpExpiryMinutes(): int
    {
        $minutes = (int) config('services.msg91.otp_expiry', 5);

        return $minutes >= 1 ? $minutes : 5;
    }

    public function resendCooldownSeconds(): int
    {
        $seconds = (int) config('services.msg91.resend_cooldown', 45);

        return $seconds >= 15 ? $seconds : 45;
    }

    /**
     * @return array{national: string, mobile: string, variants: list<string>}
     */
    public function normalizePhone(string $raw): array
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        $country = preg_replace('/\D+/', '', (string) config('services.msg91.country_code', '91')) ?: '91';

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, $country)) {
            $digits = substr($digits, strlen($country));
        }

        if (strlen($digits) !== 10 || ! preg_match('/^[6-9]/', $digits)) {
            throw Msg91OtpException::invalidPhone();
        }

        $mobile = $country.$digits;

        return [
            'national' => $digits,
            'mobile' => $mobile,
            'variants' => array_values(array_unique([
                $digits,
                $mobile,
                '+'.$mobile,
                '0'.$digits,
            ])),
        ];
    }

    public function cooldownRemaining(string $rawPhone): int
    {
        try {
            $phone = $this->normalizePhone($rawPhone);
        } catch (Msg91OtpException $e) {
            return 0;
        }

        $expiresAt = Cache::get($this->cooldownKey($phone['mobile']));
        if (! is_numeric($expiresAt)) {
            return 0;
        }

        return max(0, (int) $expiresAt - time());
    }

    public function send(string $rawPhone): array
    {
        $this->assertAvailable();
        $phone = $this->normalizePhone($rawPhone);
        $this->assertCanSend($phone['mobile']);

        if ($this->isDummy()) {
            return $this->sendDummy($phone);
        }

        $response = $this->request('post', '/otp', [
            'template_id' => (string) config('services.msg91.template_id'),
            'mobile' => $phone['mobile'],
            'otp_expiry' => $this->otpExpiryMinutes(),
            'otp_length' => $this->otpLength(),
            'realTimeResponse' => '1',
        ], $phone['mobile'], 'send');

        $this->assertProviderOk($response, 'send');
        $this->markSent($phone['mobile']);

        return [
            'phone' => $phone['national'],
            'cooldown' => $this->resendCooldownSeconds(),
            'expires_in' => $this->otpExpiryMinutes() * 60,
        ];
    }

    public function resend(string $rawPhone): array
    {
        $this->assertAvailable();
        $phone = $this->normalizePhone($rawPhone);
        $this->assertPending($phone['mobile']);
        $this->assertCanSend($phone['mobile']);

        if ($this->isDummy()) {
            return $this->sendDummy($phone);
        }

        $response = $this->request('get', '/otp/retry', [
            'mobile' => $phone['mobile'],
            'retrytype' => 'text',
        ], $phone['mobile'], 'resend');

        $this->assertProviderOk($response, 'resend');
        $this->markSent($phone['mobile'], true);

        return [
            'phone' => $phone['national'],
            'cooldown' => $this->resendCooldownSeconds(),
            'expires_in' => $this->otpExpiryMinutes() * 60,
        ];
    }

    public function verify(string $rawPhone, string $otp): array
    {
        $this->assertAvailable();
        $phone = $this->normalizePhone($rawPhone);
        $code = preg_replace('/\D+/', '', $otp) ?? '';

        if (strlen($code) !== $this->otpLength()) {
            throw Msg91OtpException::invalidOtp();
        }

        $this->assertPending($phone['mobile']);
        $this->assertNotReused($phone['mobile'], $code);
        $this->assertCanVerify($phone['mobile']);

        if ($this->isDummy()) {
            return $this->verifyDummy($phone, $code);
        }

        $response = $this->request('get', '/otp/verify', [
            'mobile' => $phone['mobile'],
            'otp' => $code,
        ], $phone['mobile'], 'verify');

        if (! $this->isSuccess($response)) {
            RateLimiter::hit($this->verifyAttemptKey($phone['mobile']), 15 * 60);
            throw $this->verificationException($response);
        }

        $this->markVerified($phone['mobile'], $code);

        return $phone;
    }

    /**
     * @param  array{national: string, mobile: string, variants: list<string>}  $phone
     * @return array{phone: string, cooldown: int, expires_in: int, dummy: bool}
     */
    protected function sendDummy(array $phone): array
    {
        $code = $this->dummyCode();
        Cache::put(
            $this->dummyCodeKey($phone['mobile']),
            hash('sha256', $code),
            now()->addMinutes($this->otpExpiryMinutes())
        );
        $this->markSent($phone['mobile']);

        return [
            'phone' => $phone['national'],
            'cooldown' => $this->resendCooldownSeconds(),
            'expires_in' => $this->otpExpiryMinutes() * 60,
            'dummy' => true,
        ];
    }

    /**
     * @param  array{national: string, mobile: string, variants: list<string>}  $phone
     * @return array{national: string, mobile: string, variants: list<string>}
     */
    protected function verifyDummy(array $phone, string $code): array
    {
        $stored = Cache::get($this->dummyCodeKey($phone['mobile']));
        if (! is_string($stored) || ! hash_equals($stored, hash('sha256', $code))) {
            RateLimiter::hit($this->verifyAttemptKey($phone['mobile']), 15 * 60);
            throw Msg91OtpException::invalidOtp();
        }

        Cache::forget($this->dummyCodeKey($phone['mobile']));
        $this->markVerified($phone['mobile'], $code);

        return $phone;
    }

    public function findUserByPhone(array $phone): ?User
    {
        return User::query()
            ->whereIn('phone', $phone['variants'])
            ->first();
    }

    protected function assertAvailable(): void
    {
        if (! $this->isAvailable()) {
            throw Msg91OtpException::notConfigured();
        }
    }

    protected function assertCanSend(string $mobile): void
    {
        $remaining = $this->cooldownRemainingFromMobile($mobile);
        if ($remaining > 0) {
            throw Msg91OtpException::cooldown($remaining);
        }

        $hourlyKey = 'msg91-otp-send-hour:'.$mobile;
        if (RateLimiter::tooManyAttempts($hourlyKey, (int) config('services.msg91.send_hourly_limit', 5))) {
            throw Msg91OtpException::sendLimit();
        }
    }

    protected function assertCanVerify(string $mobile): void
    {
        $max = (int) config('services.msg91.verify_max_attempts', 5);
        if (RateLimiter::tooManyAttempts($this->verifyAttemptKey($mobile), $max)) {
            throw Msg91OtpException::verifyLimit();
        }
    }

    protected function assertPending(string $mobile): void
    {
        if (! Cache::has($this->pendingKey($mobile))) {
            throw Msg91OtpException::notSent();
        }
    }

    protected function assertNotReused(string $mobile, string $otp): void
    {
        if (Cache::has($this->usedKey($mobile, $otp))) {
            throw Msg91OtpException::alreadyUsed();
        }
    }

    protected function markSent(string $mobile, bool $resend = false): void
    {
        $ttl = now()->addMinutes($this->otpExpiryMinutes());
        Cache::put($this->pendingKey($mobile), 1, $ttl);
        Cache::put($this->cooldownKey($mobile), time() + $this->resendCooldownSeconds(), now()->addSeconds($this->resendCooldownSeconds()));
        RateLimiter::hit('msg91-otp-send-hour:'.$mobile, 3600);
    }

    protected function markVerified(string $mobile, string $otp): void
    {
        Cache::forget($this->pendingKey($mobile));
        Cache::put($this->usedKey($mobile, $otp), 1, now()->addMinutes($this->otpExpiryMinutes() + 10));
        RateLimiter::clear($this->verifyAttemptKey($mobile));
    }

    /**
     * @param  array<string, scalar>  $payload
     * @return array<string, mixed>
     */
    protected function request(string $method, string $path, array $payload, string $mobile, string $action): array
    {
        $url = rtrim((string) config('services.msg91.base_url'), '/').$path;
        $timeout = max(5, (int) config('services.msg91.timeout', 15));

        try {
            $pending = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'authkey' => (string) config('services.msg91.auth_key'),
                ]);

            $response = $method === 'get'
                ? $pending->get($url, $payload)
                : $pending->asJson()->post($url, $payload);
        } catch (ConnectionException $e) {
            $this->logFailure($action, $mobile, null, 'timeout');
            throw Msg91OtpException::timeout();
        } catch (\Throwable $e) {
            $this->logFailure($action, $mobile, null, 'exception');
            throw Msg91OtpException::providerFailure();
        }

        $json = $response->json();
        $body = is_array($json) ? $json : ['message' => trim((string) $response->body()), 'type' => $response->successful() ? 'success' : 'error'];

        if ($response->status() === 429) {
            $this->logFailure($action, $mobile, $response->status(), 'rate_limited');
            throw Msg91OtpException::sendLimit();
        }

        if ($response->serverError()) {
            $this->logFailure($action, $mobile, $response->status(), 'server_error');
            throw Msg91OtpException::providerFailure();
        }

        $body['_http_status'] = $response->status();

        return $body;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function assertProviderOk(array $response, string $action): void
    {
        if ($this->isSuccess($response)) {
            return;
        }

        $message = strtolower((string) ($response['message'] ?? ''));
        if (str_contains($message, 'max') || str_contains($message, 'limit') || str_contains($message, 'too many')) {
            throw Msg91OtpException::sendLimit();
        }

        $this->logFailure($action, null, $response['_http_status'] ?? null, $this->safeProviderType($response));
        throw Msg91OtpException::providerFailure();
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function verificationException(array $response): Msg91OtpException
    {
        $message = strtolower((string) ($response['message'] ?? ''));

        if (str_contains($message, 'expir')) {
            return Msg91OtpException::expiredOtp();
        }
        if (str_contains($message, 'already') || str_contains($message, 'verified')) {
            return Msg91OtpException::alreadyUsed();
        }
        if (str_contains($message, 'not match') || str_contains($message, 'invalid') || str_contains($message, 'incorrect')) {
            return Msg91OtpException::invalidOtp();
        }

        return Msg91OtpException::invalidOtp();
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function isSuccess(array $response): bool
    {
        $type = strtolower((string) ($response['type'] ?? ''));
        $message = strtolower((string) ($response['message'] ?? ''));

        return $type === 'success'
            || str_contains($message, 'success')
            || str_contains($message, 'otp sent')
            || str_contains($message, 'verified');
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function safeProviderType(array $response): string
    {
        $type = strtolower((string) ($response['type'] ?? 'error'));

        return in_array($type, ['success', 'error', 'warning'], true) ? $type : 'error';
    }

    protected function logFailure(string $action, ?string $mobile, $status, string $reason): void
    {
        Log::warning('MSG91 OTP request failed', [
            'action' => $action,
            'mobile' => $mobile ? $this->maskMobile($mobile) : null,
            'status' => $status,
            'reason' => $reason,
        ]);
    }

    protected function maskMobile(string $mobile): string
    {
        if (strlen($mobile) < 4) {
            return '****';
        }

        return str_repeat('*', max(0, strlen($mobile) - 4)).substr($mobile, -4);
    }

    protected function cooldownRemainingFromMobile(string $mobile): int
    {
        $expiresAt = Cache::get($this->cooldownKey($mobile));
        if (! is_numeric($expiresAt)) {
            return 0;
        }

        return max(0, (int) $expiresAt - time());
    }

    protected function dummyCodeKey(string $mobile): string
    {
        return 'msg91:otp:dummy:'.$mobile;
    }

    protected function pendingKey(string $mobile): string
    {
        return 'msg91:otp:pending:'.$mobile;
    }

    protected function cooldownKey(string $mobile): string
    {
        return 'msg91:otp:cooldown:'.$mobile;
    }

    protected function usedKey(string $mobile, string $otp): string
    {
        return 'msg91:otp:used:'.$mobile.':'.hash('sha256', $otp);
    }

    protected function verifyAttemptKey(string $mobile): string
    {
        return 'msg91-otp-verify:'.$mobile;
    }
}
