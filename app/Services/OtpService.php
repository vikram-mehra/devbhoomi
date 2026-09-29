<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OtpService
{
    public function send(string $phone): string
    {
        $code = (string) random_int(100000, 999999);
        Cache::put('otp:'.$phone, $code, now()->addMinutes(10));

        return $code;
    }

    public function verify(string $phone, string $code): bool
    {
        $cached = Cache::get('otp:'.$phone);
        if ($cached && hash_equals((string) $cached, (string) $code)) {
            Cache::forget('otp:'.$phone);

            return true;
        }

        return false;
    }
}
