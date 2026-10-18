<?php

return [
    'enabled' => env('QUERY_DOCTOR_ENABLED', false),
    'slow_query_threshold_ms' => 200,
    'repeated_query_threshold' => 5,
    'retention_days' => 7,
    'max_queries_per_context' => 1000,
    'max_sql_length' => 2048,
    'storage_connection' => env('QUERY_DOCTOR_DB_CONNECTION'),
    'allow_production' => env('QUERY_DOCTOR_ALLOW_PRODUCTION', false),
];
