<?php

return [
    // Prefer standard APP_ENV ("production", "local", "staging").
    // ENVIRONMENT is kept for backward compatibility (e.g. "Production").
    'environment' => env('ENVIRONMENT', env('APP_ENV', 'production')),
    'backend_access_domain' => env('BACKEND_ACCESS_DOMAIN'),
    'company_id' => env('COMPANY_ID'),
    'assets_url' => env('ASSETS_URL'),
    'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY'),
    'recaptcha_secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'author' => env('AUTHOR'),
    'author_url' => env('AUTHOR_URL'),
    'app_name' => env('APP_NAME'),
    'cache_minutes' => env('CACHE_MINUTES', 120),
    'from_email' => env('MAIL_FROM_ADDRESS'),
    'tinymce_api' => env('TINYMCE_API_KEY'),
    'pagination_per_page' => env('PAGINATION_PER_PAGE', 25),
    'pagination_per_media_page' => env('PAGINATION_PER_MEDIA_PAGE', 72),
    'frontend_cache_clear_url' => env('FRONTEND_CACHE_CLEAR_URL'),
    'frontend_url' => env('FRONTEND_URL'),
    'frontend_sitemap_generate_url' => env('FRONTEND_SITEMAP_GENERATE_URL'),
    'frontend_robots_generate_url' => env('FRONTEND_ROBOTS_GENERATE_URL'),
];
