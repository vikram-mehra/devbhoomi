<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP lifetime (minutes) — default 10 per requirements
    |--------------------------------------------------------------------------
    */
    'token_expire_minutes' => (int) env('VERIFICATION_TOKEN_EXPIRE', 10),

    'code_length' => (int) env('VERIFICATION_CODE_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | Max wrong OTP attempts before user must resend
    |--------------------------------------------------------------------------
    */
    'max_attempts' => (int) env('VERIFICATION_MAX_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | Minimum seconds between resend requests (per email)
    |--------------------------------------------------------------------------
    */
    'resend_cooldown_seconds' => (int) env('VERIFICATION_RESEND_COOLDOWN', 60),

    /*
    |--------------------------------------------------------------------------
    | Log the user in automatically after successful verification
    |--------------------------------------------------------------------------
    */
    'auto_login_after_verify' => filter_var(env('VERIFICATION_AUTO_LOGIN', true), FILTER_VALIDATE_BOOLEAN),

    'support_email' => env('MAIL_SUPPORT_ADDRESS', env('MAIL_FROM_ADDRESS', 'support@example.com')),

    /*
    |--------------------------------------------------------------------------
    | On local only: show OTP on screen when SMTP is not set up
    |--------------------------------------------------------------------------
    */
    'local_code_fallback' => filter_var(env('VERIFICATION_LOCAL_CODE_FALLBACK', true), FILTER_VALIDATE_BOOLEAN),

];
