<?php

declare(strict_types=1);

return [
    // Единый ключ 'driver' для всех репозиториев config | eloquent
    'driver' => env('FEATURE_FLAGS_DRIVER', 'eloquent'),

    // Путь к файлу с правилами (используется при driver='config')
    'config_path' => __DIR__ . '/feature_flags_rules.php',

    // Опционально: логирование статистики
    'log_statistics' => env('FEATURE_FLAGS_LOGGER', false),

    'statistics' => [
        'per_page' => (int) env('FEATURE_FLAGS_STATISTICS_PER_PAGE', 111),
        // Максимальное значение (защита от злоупотреблений)
        'max_per_page' => (int) env('FEATURE_FLAGS_STATISTICS_MAX_PER_PAGE', 200),
    ],
];
