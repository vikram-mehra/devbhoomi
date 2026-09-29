<?php

return [

    'default' => env('DEFAULT_COURIER', 'delhivery'),

    /*
    | Queue bulk shipment creation when the queue driver is not "sync".
    */
    'queue' => filter_var(env('COURIER_QUEUE', true), FILTER_VALIDATE_BOOLEAN),

    'package_defaults' => [
        'weight' => (float) env('COURIER_DEFAULT_WEIGHT', 0.5),
        'length' => (float) env('COURIER_DEFAULT_LENGTH', 14),
        'width' => (float) env('COURIER_DEFAULT_WIDTH', 10),
        'height' => (float) env('COURIER_DEFAULT_HEIGHT', 6),
        'hsn' => env('COURIER_DEFAULT_HSN', '21069099'),
    ],

    'pickup' => [
        'name' => env('COURIER_PICKUP_NAME', env('APP_NAME', 'Warehouse')),
        'phone' => env('COURIER_PICKUP_PHONE', ''),
        'email' => env('COURIER_PICKUP_EMAIL', ''),
        'address_line_1' => env('COURIER_PICKUP_ADDRESS', ''),
        'address_line_2' => env('COURIER_PICKUP_ADDRESS_2', ''),
        'city' => env('COURIER_PICKUP_CITY', ''),
        'state' => env('COURIER_PICKUP_STATE', ''),
        'pincode' => env('COURIER_PICKUP_PINCODE', ''),
        'country' => env('COURIER_PICKUP_COUNTRY', 'India'),
    ],

    'partners' => [

        'delhivery' => [
            'name' => 'Delhivery',
            'enabled' => filter_var(env('COURIER_DELHIVERY_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
            'adapter' => App\Services\Courier\Adapters\DelhiveryAdapter::class,
            'api_url' => env('DELHIVERY_API_URL') ?: env('DELHIVERY_BASE_URL') ?: 'https://staging-express.delhivery.com',
            'api_token' => env('DELHIVERY_API_TOKEN') ?: env('DELHIVERY_TOKEN') ?: env('DELHIVERY_TEST_TOKEN') ?: env('DELHIVERY_LIVE_TOKEN'),
            'warehouse' => env('DELHIVERY_WAREHOUSE') ?: env('COURIER_PICKUP_NAME'),
            'client' => env('DELHIVERY_CLIENT'),
            'timeout' => (int) env('DELHIVERY_TIMEOUT', 30),
            'webhook_token' => env('DELHIVERY_WEBHOOK_TOKEN'),
        ],

        'bluedart' => [
            'name' => 'Blue Dart',
            'enabled' => filter_var(env('COURIER_BLUEDART_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
            'adapter' => App\Services\Courier\Adapters\BlueDartAdapter::class,
            'api_url' => env('BLUEDART_API_URL', 'https://apigateway.bluedart.com'),
            'username' => env('BLUEDART_USERNAME'),
            'password' => env('BLUEDART_PASSWORD'),
            'client_id' => env('BLUEDART_CLIENT_ID'),
            'client_secret' => env('BLUEDART_CLIENT_SECRET'),
            'licence_key' => env('BLUEDART_LICENCE_KEY'),
            'login_id' => env('BLUEDART_LOGIN_ID'),
            'timeout' => (int) env('BLUEDART_TIMEOUT', 30),
            'webhook_token' => env('BLUEDART_WEBHOOK_TOKEN'),
        ],

    ],

];
