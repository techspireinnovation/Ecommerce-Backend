<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Apply CORS to all API routes
    'paths' => ['api/*'],

    // Allow all HTTP methods (GET, POST, PUT, DELETE, etc.)
    'allowed_methods' => ['*'],

    // Allowed frontend URLs (replace with your frontend domain in production)
    'allowed_origins' => ['http://localhost:3000'], // example frontend

    'allowed_origins_patterns' => [],

    // Headers allowed in requests
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept'],

    // Headers you want to expose to frontend (optional)
    'exposed_headers' => ['Authorization'],

    // How long the results of a preflight request can be cached
    'max_age' => 0,

    // JWT does NOT use cookies, so credentials should be false
    'supports_credentials' => false,
];
