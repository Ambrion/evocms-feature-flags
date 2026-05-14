<?php

declare(strict_types=1);

return [
    // Единый ключ 'driver' для всех репозиториев config | eloquent
    'driver' => env('FEATURE_FLAGS_DRIVER', 'eloquent'),

    // Путь к файлу с правилами (используется при driver='config')
    'config_path' => env(
        'FEATURE_FLAGS_CONFIG_PATH',
        defined('MODX_BASE_PATH')
            ? MODX_BASE_PATH . '/assets/modules/feature_flags/config/feature_flags_rules.php'
            : __DIR__ . '/../publishable/assets/modules/feature_flags/config/feature_flags_rules.php'
    ),

    // Опционально: логирование статистики
    'log_statistics' => env('FEATURE_FLAGS_LOGGER', false),

    'statistics' => [
        'per_page' => (int) env('FEATURE_FLAGS_STATISTICS_PER_PAGE', 111),
        // Максимальное значение (защита от злоупотреблений)
        'max_per_page' => (int) env('FEATURE_FLAGS_STATISTICS_MAX_PER_PAGE', 200),
    ],
];
