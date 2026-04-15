<?php

declare(strict_types=1);

return [
    'enable' => (bool) env('ENABLE_QUERY_LOGGING', false),
    'slow_threshold' => (int) env('LOG_QUERIES_SLOW_THRESHOLD', 100),
    'log_n_plus_one' => (bool) env('LOG_QUERIES_N_PLUS_ONE', false),
];
