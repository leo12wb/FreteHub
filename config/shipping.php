<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Carrier Failure Rate (%)
    |--------------------------------------------------------------------------
    | Percentage chance (0–100) that a mock carrier throws an exception.
    | Set to 0 in testing environments to guarantee deterministic results.
    */
    'carrier_failure_rate' => (int) env('CARRIER_FAILURE_RATE', 5),

    /*
    |--------------------------------------------------------------------------
    | Quote Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */
    'quote_cache_ttl' => (int) env('QUOTE_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Quote Expiry (minutes)
    |--------------------------------------------------------------------------
    */
    'quote_expires_in_minutes' => (int) env('QUOTE_EXPIRES_IN_MINUTES', 60),
];
