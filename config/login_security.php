<?php

return [
    'max_failed_attempts' => (int) env('LOGIN_MAX_FAILED_ATTEMPTS', 3),
    'lock_minutes' => (int) env('LOGIN_LOCK_MINUTES', 15),
    'max_requests_per_minute' => (int) env('LOGIN_RATE_LIMIT_PER_MINUTE', 20),
    'rate_limit_decay_seconds' => (int) env('LOGIN_RATE_LIMIT_DECAY_SECONDS', 60),
];
