<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Set `CORS_ALLOWED_ORIGINS` in your .env as a comma-separated list:
    |   CORS_ALLOWED_ORIGINS=https://example.com,https://admin.example.com
    |
    | If you enable credentials, you must use explicit origins (no "*").
    |
    */

    'paths' => array_filter(array_map('trim', explode(',', env('CORS_PATHS', 'api/*')))),

    'allowed_methods' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_METHODS', '*')))),

    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '')))),

    'allowed_origins_patterns' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS_PATTERNS', '')))),

    'allowed_headers' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_HEADERS', '*')))),

    'exposed_headers' => array_filter(array_map('trim', explode(',', env('CORS_EXPOSED_HEADERS', '')))),

    'max_age' => (int) env('CORS_MAX_AGE', 0),

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', true),
];

