<?php

namespace App\Exceptions;

use RuntimeException;

class Msg91OtpException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self(__('Mobile OTP login is not configured yet. Please use email or Google sign-in.'));
    }

    public static function invalidPhone(): self
    {
        return new self(__('Kindly enter a valid mobile number'));
    }

    public static function cooldown(int $seconds): self
    {
        return new self(__('Please wait :seconds seconds before requesting another OTP.', [
            'seconds' => max(1, $seconds),
        ]));
    }

    public static function sendLimit(): self
    {
        return new self(__('Too many OTP requests for this number. Try again later.'));
    }

    public static function verifyLimit(): self
    {
        return new self(__('Too many incorrect attempts. Request a new OTP.'));
    }

    public static function invalidOtp(): self
    {
        return new self(__('The OTP is invalid. Please check the code and try again.'));
    }

    public static function expiredOtp(): self
    {
        return new self(__('This OTP has expired. Please request a new one.'));
    }

    public static function alreadyUsed(): self
    {
        return new self(__('This OTP has already been used. Please request a new one.'));
    }

    public static function notSent(): self
    {
        return new self(__('Request an OTP first, then enter the code we sent you.'));
    }

    public static function providerFailure(?string $safeMessage = null): self
    {
        return new self($safeMessage ?: __('We could not send the OTP right now. Please try again.'));
    }

    public static function timeout(): self
    {
        return new self(__('The OTP service timed out. Please try again.'));
    }

    public static function privilegedAccount(): self
    {
        return new self(__('Please use the admin login page for this account.'));
    }
}
