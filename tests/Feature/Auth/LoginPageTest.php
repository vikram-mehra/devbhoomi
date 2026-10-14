<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_keeps_email_google_and_adds_mobile_otp(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('action="'.route('login').'"', $html);
        $this->assertStringContainsString(__('Mobile OTP'), $html);
        $this->assertStringContainsString('/login/phone/send', $html);
        $this->assertStringContainsString(route('auth.google'), $html);
        $this->assertStringContainsString('id="otpSendBtn"', $html);
        $this->assertMatchesRegularExpression('/id="otpSendBtn"[^>]*\sdisabled/', $html);
        $this->assertStringContainsString('id="otpVerifyModal"', $html);
        $this->assertStringContainsString('otp-digit-box', $html);
        $this->assertStringContainsString(__('Edit mobile'), $html);
        $this->assertStringContainsString(__('Verify mobile'), $html);
    }

    public function test_login_page_does_not_reopen_otp_modal_from_previous_session(): void
    {
        $html = $this->withSession(['otp_login_phone' => '9876543210'])
            ->get(route('login'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('value="9876543210"', $html);
        $this->assertStringContainsString('openOnLoad = false', $html);
        $this->assertStringNotContainsString('+91 98765 43210', $html);
    }

    public function test_register_page_is_still_available(): void
    {
        $this->get(route('register'))->assertOk();
    }
}
