<?php

declare(strict_types=1);

return [
    'source' => env('SERVICE_NAME', 'identity'),

    'consumer_group' => env('KAFKA_CONSUMER_GROUP_ID', env('SERVICE_NAME', 'identity')),

    // Event type => handler. Every type listed here needs its topic in deploy/values.yaml → consumers.
    // identity consumes nothing (ADR-0001).
    'handlers' => [],

    'max_attempts' => 3,

    'relay_batch_size' => 100,
];
