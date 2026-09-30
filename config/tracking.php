<?php

$env = strtolower((string) env('DELHIVERY_ENV', 'test')) === 'live' ? 'live' : 'test';
$defaultUrl = $env === 'live'
    ? env('DELHIVERY_LIVE_URL', 'https://track.delhivery.com')
    : env('DELHIVERY_TEST_URL', 'https://staging-express.delhivery.com');
$fallbackToken = $env === 'live' ? env('DELHIVERY_LIVE_TOKEN') : env('DELHIVERY_TEST_TOKEN');

return [

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
