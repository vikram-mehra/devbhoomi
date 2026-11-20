<?php

namespace Tests\Unit\Auth;

use App\Exceptions\Msg91OtpException;
use App\Services\Msg91OtpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class Msg91OtpServiceTest extends TestCase
{
    protected Msg91OtpService $otp;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.msg91', [
            'auth_key' => 'test-auth-key',
            'template_id' => 'test-template',
            'otp_expiry' => 5,
            'otp_length' => 6,
            'enabled' => true,
            'dummy' => false,
            'dummy_code' => '123456',
            'country_code' => '91',
            'base_url' => 'https://control.msg91.com/api/v5',
            'timeout' => 5,
            'resend_cooldown' => 45,
            'send_hourly_limit' => 5,
            'verify_max_attempts' => 5,
        ]);

        Cache::flush();
        $this->otp = new Msg91OtpService();
    }

    public function test_normalizes_indian_mobile_numbers(): void
    {
        $this->assertSame('9876543210', $this->otp->normalizePhone('9876543210')['national']);
        $this->assertSame('919876543210', $this->otp->normalizePhone('+91 98765 43210')['mobile']);
        $this->assertSame('9876543210', $this->otp->normalizePhone('09876543210')['national']);
    }

    public function test_rejects_invalid_phone_numbers(): void
    {
        $this->expectException(Msg91OtpException::class);
        $this->otp->normalizePhone('12345');
    }

    public function test_send_uses_msg91_otp_api_without_client_generated_otp(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
        ]);

        $result = $this->otp->send('9876543210');

        $this->assertSame('9876543210', $result['phone']);
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://control.msg91.com/api/v5/otp'
                && $request->hasHeader('authkey', 'test-auth-key')
                && ($data['mobile'] ?? null) === '919876543210'
                && ($data['template_id'] ?? null) === 'test-template'
                && ! array_key_exists('otp', $data);
        });
    }

    public function test_verify_uses_official_verify_endpoint(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'success', 'message' => 'OTP verified success'], 200),
        ]);

        $this->otp->send('9876543210');
        $phone = $this->otp->verify('9876543210', '123456');

        $this->assertSame('919876543210', $phone['mobile']);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/otp/verify')
                && str_contains($request->url(), 'mobile=919876543210');
        });
    }

    public function test_invalid_otp_is_rejected(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'error', 'message' => 'OTP not match'], 200),
        ]);

        $this->otp->send('9876543210');

        $this->expectException(Msg91OtpException::class);
        $this->otp->verify('9876543210', '000000');
    }

    public function test_expired_otp_is_rejected(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'error', 'message' => 'OTP expired'], 200),
        ]);

        $this->otp->send('9876543210');

        try {
            $this->otp->verify('9876543210', '123456');
            $this->fail('Expected expired OTP to fail.');
        } catch (Msg91OtpException $e) {
            $this->assertStringContainsString('expired', strtolower($e->getMessage()));
        }
    }

    public function test_otp_cannot_be_reused_after_success(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'success', 'message' => 'OTP verified success'], 200),
        ]);

        $this->otp->send('9876543210');
        $this->otp->verify('9876543210', '123456');

        $this->expectException(Msg91OtpException::class);
        $this->otp->verify('9876543210', '123456');
    }

    public function test_resend_cooldown_is_enforced(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
        ]);

        $this->otp->send('9876543210');

        $this->expectException(Msg91OtpException::class);
        $this->otp->send('9876543210');
    }

    public function test_disabled_integration_does_not_call_msg91(): void
    {
        config()->set('services.msg91.enabled', false);
        Http::fake();

        try {
            $this->otp->send('9876543210');
            $this->fail('Expected disabled OTP to fail.');
        } catch (Msg91OtpException $e) {
            Http::assertNothingSent();
        }
    }

    public function test_failures_do_not_log_otp_or_auth_key(): void
    {
        Log::spy();
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'error', 'message' => 'gateway error'], 500),
        ]);

        try {
            $this->otp->send('9876543210');
        } catch (Msg91OtpException $e) {
            // expected
        }

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context) {
            $encoded = json_encode($context);

            return $message === 'MSG91 OTP request failed'
                && ! str_contains($encoded, 'test-auth-key')
                && ! str_contains(strtolower($encoded), 'otp=');
        })->once();
    }

    public function test_dummy_mode_is_available_even_when_enabled_flag_is_off(): void
    {
        config()->set('services.msg91.enabled', false);
        config()->set('services.msg91.dummy', true);
        config()->set('services.msg91.template_id', '');

        $this->assertTrue($this->otp->isAvailable());
    }

    public function test_dummy_mode_verifies_without_calling_msg91(): void
    {
        config()->set('services.msg91.dummy', true);
        config()->set('services.msg91.template_id', '');
        Http::fake();

        $this->otp->send('9876543210');
        $phone = $this->otp->verify('9876543210', '123456');

        $this->assertSame('9876543210', $phone['national']);
        Http::assertNothingSent();
    }

    public function test_empty_template_id_uses_dummy_mode(): void
    {
        config()->set('services.msg91.dummy', false);
        config()->set('services.msg91.template_id', '');
        Http::fake();

        $this->assertTrue($this->otp->isDummy());
        $this->otp->send('9876543210');
        Http::assertNothingSent();
    }
}
