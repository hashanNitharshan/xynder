<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    | Handled ONLY by Laravel — Nginx must NOT add any Access-Control headers
    | or the browser will receive duplicate values and block all requests.
    |
    | supports_credentials MUST be false when allowed_origins is ['*'].
    | The APK uses Bearer tokens (not cookies) so credentials are not needed.
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];