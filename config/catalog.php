<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Homepage product cache (seconds)
    |--------------------------------------------------------------------------
    | 0 = always fresh (recommended on local). Cleared automatically on product CRUD.
    */
    'home_cache_seconds' => (int) env('HOME_CACHE_SECONDS', env('APP_ENV') === 'local' ? 0 : 600),

];
