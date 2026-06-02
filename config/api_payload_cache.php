<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public API payload cache TTL
    |--------------------------------------------------------------------------
    |
    | Duration in seconds that a cached JSON payload for Page / Menu / Company
    | responses is stored. Default: 7 days.
    |
    */
    'ttl_seconds' => (int) env('API_PAYLOAD_CACHE_TTL', 60 * 60 * 24 * 7),

    /*
    |--------------------------------------------------------------------------
    | Revision key lifetime
    |--------------------------------------------------------------------------
    |
    | Invalidation uses small integer counters (not full API payloads). Most
    | cache size comes from payload entries above, which still expire after
    | ttl_seconds (e.g. 7 days). Keep this longer than ttl_seconds so a bump
    | counter does not disappear before payloads do — default 30 days below.
    |
    */
    'revision_ttl_seconds' => (int) env('API_PAYLOAD_CACHE_REVISION_TTL', 60 * 60 * 24 * 30),

    'key_version' => env('API_PAYLOAD_CACHE_KEY_VERSION', 'v1'),

];
