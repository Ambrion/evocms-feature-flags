<?php
/**
 * Сниппет: FeatureFlags
 * Вызов: [!FeatureFlags? &flag=`show_new_year_banner` &yes=`<span class="badge">NEW</span>` &no=``!]
 * Параметры: &flag, &context (JSON), &yes, &no
 */

use EvolutionCMS\FeatureFlags\Infrastructure\Context\ModxContextMapper;
use FeatureFlags\Core\Application\Service\FeatureFlagService;

// 1. Безопасное чтение параметров
$flagName = trim($params['flag'] ?? '');
$yesOutput = $params['yes'] ?? '1';
$noOutput = $params['no'] ?? '0';
$customContextJson = $params['context'] ?? '';

if ($flagName === '') {
    return $noOutput;
}

// 2. Собираем контекст доступный в EvolutionCMS
$rawModxData = [
    'target_id'    => (string) (evo()->documentIdentifier ?? 0),
    'user_role'    => evo()->getLoginUserID() ? 'member' : 'guest',
    'environment'  => (evo()->config['site_status'] ?? '1') == '0' ? 'maintenance' : 'production',
    'current_date' => date('Y-m-d'),
    'user_hash'    => md5($_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?? '')),
];

// 3. Мержим кастомный контекст из JSON-параметра
if ($customContextJson !== '') {
    $custom = json_decode($customContextJson, true) ?: [];
    $rawModxData = array_merge($rawModxData, $custom);
}

// 4. Используем маппер для подготовки контекста
$mapper = new ModxContextMapper($rawModxData);
$context = $mapper->build();

// 5. Вычисляем флаг
try {
    // Получаем сервис из DI-контейнера Evo
    /** @var FeatureFlagService $service */
    $service = evo()->make(FeatureFlagService::class);
    $isActive = $service->isEnabled($flagName, $context);
    return $isActive ? $yesOutput : $noOutput;
} catch (Throwable $e) {
    return $noOutput;
}
