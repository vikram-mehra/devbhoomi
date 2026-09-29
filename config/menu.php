<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu cache TTL (seconds)
    |--------------------------------------------------------------------------
    | Set to 0 for instant updates (recommended on local / while editing menus).
    | On production, use 3600 or higher; cache is cleared automatically on CRUD.
    */
    'cache_seconds' => (int) env('MENU_CACHE_SECONDS', env('APP_ENV') === 'local' ? 0 : 3600),

];
