<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // SOW B.01 — author sign-in with Google and ORCID (Socialite).
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'orcid' => [
        'client_id' => env('ORCID_CLIENT_ID'),
        'client_secret' => env('ORCID_CLIENT_SECRET'),
        'redirect' => env('ORCID_REDIRECT_URI'),
        'environment' => env('ORCID_ENVIRONMENT', 'sandbox'),
    ],

    // SOW Inclusions — Razorpay is the payment gateway. Without keys (local/testing)
    // the app falls back to a simulated gateway so the workflow can be exercised.
    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    // SOW A.18 — plagiarism checker. "originality" = Originality.ai API v3 (https://docs.originality.ai);
    // "fake" returns a simulated result (local development and tests).
    'plagiarism' => [
        'driver' => env('PLAGIARISM_DRIVER', 'fake'),
        'api_url' => env('PLAGIARISM_API_URL', 'https://api.originality.ai/api/v3'),
        'api_key' => env('PLAGIARISM_API_KEY'),
        // Long manuscripts are scanned in chunks of this many words (a scan can take up to 60 s).
        'chunk_words' => (int) env('PLAGIARISM_CHUNK_WORDS', 1500),
        'timeout' => (int) env('PLAGIARISM_TIMEOUT', 180),
        // Keep scans in the Originality.ai dashboard (false = results cannot be viewed there again).
        'store_scan' => (bool) env('PLAGIARISM_STORE_SCAN', true),
        // Required by the API even though AI detection is not requested.
        'ai_model' => env('PLAGIARISM_AI_MODEL', 'lite'),
        // Comma-separated URLs never counted as matches (the site itself is always excluded).
        'excluded_urls' => env('PLAGIARISM_EXCLUDED_URLS', ''),
        'fake_similarity' => env('PLAGIARISM_FAKE_SIMILARITY'),
    ],
];
