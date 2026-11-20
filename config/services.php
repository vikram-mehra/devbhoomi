<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
    ],

    'delhivery' => [
        'env' => env('DELHIVERY_ENV', 'test'),
        'token' => env('DELHIVERY_TOKEN'),
        'base_url' => env('DELHIVERY_BASE_URL'),
        'test_token' => env('DELHIVERY_TEST_TOKEN'),
        'live_token' => env('DELHIVERY_LIVE_TOKEN'),
        'webhook_token' => env('DELHIVERY_WEBHOOK_TOKEN'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim((string) env('APP_URL', 'http://localhost'), '/').'/auth/google/callback'),
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'msg91' => [
        'auth_key' => env('MSG91_AUTH_KEY'),
        'template_id' => env('MSG91_OTP_TEMPLATE_ID'),
        'otp_expiry' => (int) env('MSG91_OTP_EXPIRY', 5),
        'otp_length' => (int) env('MSG91_OTP_LENGTH', 6),
        'enabled' => filter_var(env('MSG91_OTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'dummy' => filter_var(env('MSG91_OTP_DUMMY', false), FILTER_VALIDATE_BOOLEAN),
        'dummy_code' => env('MSG91_OTP_DUMMY_CODE', '123456'),
        'country_code' => env('MSG91_COUNTRY_CODE', '91'),
        'base_url' => env('MSG91_BASE_URL', 'https://control.msg91.com/api/v5'),
        'timeout' => (int) env('MSG91_TIMEOUT', 15),
        'resend_cooldown' => (int) env('MSG91_RESEND_COOLDOWN', 45),
        'send_hourly_limit' => (int) env('MSG91_SEND_HOURLY_LIMIT', 5),
        'verify_max_attempts' => (int) env('MSG91_VERIFY_MAX_ATTEMPTS', 5),
    ],

];
