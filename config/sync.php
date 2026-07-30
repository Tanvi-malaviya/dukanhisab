<?php

return [
    // How long a completed idempotency key is kept before it can be purged.
    'idempotency_ttl_days' => env('SYNC_IDEMPOTENCY_TTL_DAYS', 7),

    // How long a "processing" idempotency key is considered a live in-flight
    // request before it's treated as orphaned (e.g. the original request crashed).
    'idempotency_stale_lock_seconds' => 30,

    // Maximum number of operations accepted per POST /v1/sync/batch request.
    'batch_max_operations' => env('SYNC_BATCH_MAX_OPERATIONS', 100),
];
