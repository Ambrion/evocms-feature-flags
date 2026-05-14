<?php
/**
 * Сниппет: FeatureFlagsDemo
 * Оценка фич-флага с поддержкой демо-контекста через GET-параметры
 *
 * Вызов: [!FeatureFlagsDemoV2? &flag=`show_new_year_banner` &yes=`<span>YES</span>` &no=`NO`]
 * Параметры:
 *   &flag    - имя флага (обязательно)
 *   &yes     - вывод если флаг активен
 *   &no      - вывод если флаг неактивен
 *   &context - JSON с дополнительным контекстом (опционально)
 */

declare(strict_types=1);

use EvolutionCMS\FeatureFlags\Infrastructure\Context\ModxContextMapper;
use FeatureFlags\Core\Application\Service\FeatureFlagService;

// 1. Чтение параметров вызова
$flagName = trim($params['flag'] ?? '');
$yesOutput = $params['yes'] ?? '1';
$noOutput = $params['no'] ?? '0';
$customContextJson = $params['context'] ?? '';

if ($flagName === '') {
    return $noOutput;
}

// 2. Базовый контекст из EvolutionCMS
$modx = evo();
$rawModxData = [
    'target_id'    => (string) ($modx->documentIdentifier ?? 0),
    'user_role'    => $modx->getLoginUserID('mgr') ? 'manager' : ($modx->getLoginUserID() ? 'user' : 'guest'),
    'environment'  => ($modx->config['site_status'] ?? '1') === '0' ? 'maintenance' : 'production',
    'current_date' => date('Y-m-d'),
    'user_hash'    => md5($_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?? '')),
    'category'     => 'general',
];

// 3. Переопределение через демо-GET параметры (из JS-панели)
$demoMap = [
    'demo_role'     => 'user_role',
    'demo_date'     => 'current_date',
    'demo_category' => 'category',
];

foreach ($demoMap as $get => $ctxKey) {
    if (isset($_GET[$get]) && $_GET[$get] !== '') {
        $rawModxData[$ctxKey] = $_GET[$get];
    }
}

// 4. Кастомный контекст из параметра &context=`{"key":"value"}`
if ($customContextJson !== '') {
    $custom = json_decode($customContextJson, true);
    if (is_array($custom)) {
        $rawModxData = array_merge($rawModxData, $custom);
    }
}

// 5. Используем маппер для подготовки контекста
$mapper = new ModxContextMapper($rawModxData);
$context = $mapper->build();

// 6. Оценка флага через сервис
try {
    $service = evo()->make(FeatureFlagService::class);
    $isActive = $service->isEnabled($flagName, $context);

    return $isActive ? $yesOutput : $noOutput;
} catch (Throwable $e) {
    // В проде лучше логировать: evo()->logEvent('feature_flags', 3, $e->getMessage());
    return $noOutput;
}
