<?php

declare(strict_types=1);

return [
    'driver' => env('FEATURE_FLAGS_DRIVER', 'eloquent'),
    'config_path' => env('FEATURE_FLAGS_CONFIG_PATH', MODX_BASE_PATH . 'assets/modules/feature_flags/config/feature_flags_rules.php'),
    'log_statistics' => env('FEATURE_FLAGS_LOGGER', false),
    'statistics' => [
        'per_page' => (int) env('FEATURE_FLAGS_STATISTICS_PER_PAGE', 50),
        'max_per_page' => (int) env('FEATURE_FLAGS_STATISTICS_MAX_PER_PAGE', 200),
    ],
];
