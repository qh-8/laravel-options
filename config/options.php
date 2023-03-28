<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Only Autoload
    |--------------------------------------------------------------------------
    |
    | Whether to load all options from database when booting.
    | If set to false, you can load options by calling `options()->load()`.
    | Default: true
    |
    */

    'only_autoload' => env('OPTIONS_ONLY_AUTOLOAD', true),

    /*
    |--------------------------------------------------------------------------
    | Eager load
    |--------------------------------------------------------------------------
    |
    | Whether to get option from database when it is not found in cache.
    | Default: true
    |
    */

    'eager_load' => env('OPTIONS_EAGER_LOAD', true),

];
