<?php

$env = strtolower((string) env('DELHIVERY_ENV', 'test')) === 'live' ? 'live' : 'test';
$defaultUrl = $env === 'live'
    ? env('DELHIVERY_LIVE_URL', 'https://track.delhivery.com')
    : env('DELHIVERY_TEST_URL', 'https://staging-express.delhivery.com');
$fallbackToken = $env === 'live' ? env('DELHIVERY_LIVE_TOKEN') : env('DELHIVERY_TEST_TOKEN');

return [

    /*
    |--------------------------------------------------------------------------
    | Dummy tracking credentials
    |--------------------------------------------------------------------------
    |
    | Storefront preview when no real order / AWB is on file. Not used for
    | live customer orders.
    |
    */
    'dummy' => filter_var(env('TRACKING_DUMMY', true), FILTER_VALIDATE_BOOLEAN),

    'dummy_order' => env('TRACKING_DUMMY_ORDER', '100001'),

    'dummy_email' => env('TRACKING_DUMMY_EMAIL', 'track@demo.test'),

    'dummy_phone' => env('TRACKING_DUMMY_PHONE', '9999999999'),

    'dummy_courier' => env('TRACKING_DUMMY_COURIER', 'Delhivery'),

    'dummy_awb' => env('TRACKING_DUMMY_AWB', 'DBN7X4K9Q2M'),

    /*
    |--------------------------------------------------------------------------
    | Delhivery
    |--------------------------------------------------------------------------
    |
    | Primary keys: DELHIVERY_TOKEN + DELHIVERY_BASE_URL.
    | Older TEST/LIVE keys remain as fallbacks.
    |
    */
    'delhivery' => [
        'env' => $env,
        'token' => env('DELHIVERY_TOKEN', $fallbackToken),
        'base_url' => env('DELHIVERY_BASE_URL', $defaultUrl),
        'test_url' => env('DELHIVERY_TEST_URL', 'https://staging-express.delhivery.com'),
        'live_url' => env('DELHIVERY_LIVE_URL', 'https://track.delhivery.com'),
        'test_token' => env('DELHIVERY_TEST_TOKEN'),
        'live_token' => env('DELHIVERY_LIVE_TOKEN'),
        'webhook_token' => env('DELHIVERY_WEBHOOK_TOKEN'),
        'timeout' => (int) env('DELHIVERY_TIMEOUT', 12),
    ],

];
