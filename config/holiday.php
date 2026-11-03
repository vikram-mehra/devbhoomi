<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Holiday / Festival Mode (e.g. Diwali)
    |--------------------------------------------------------------------------
    |
    | When enabled, users cannot checkout, create orders, or complete payments.
    | A festive popup notice with an animated Diwali Diya is displayed on
    | the storefront, which can be dismissed for 1 hour by the user.
    |
    */
    'enabled' => filter_var(env('HOLIDAY_MODE_ENABLED', env('HOLIDAY_MODE', false)), FILTER_VALIDATE_BOOLEAN),

    'show_diya' => filter_var(env('HOLIDAY_MODE_DIYA', true), FILTER_VALIDATE_BOOLEAN),

    'title' => env('HOLIDAY_MODE_TITLE', 'Happy Diwali from Devbhoomi Naturals! 🪔'),

    'message' => env('HOLIDAY_MODE_MESSAGE', 'Wishing you and your family a joyful, healthy, and prosperous Diwali! Our team is taking a short festival break. You can explore all our natural Himalayan products and add them to your cart, but checkout and dispatch will resume right after the festivities!'),

];
