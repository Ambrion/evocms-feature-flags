<?php
/**
 * Сниппет: ABVariantTest
 * A/B/C-тестирование контента с поддержкой чанков и файлов
 *
 * Параметры вызова:
 * [!ABVariantTest?
 *   &flag=`my_ab_test`
 *   &a=`ChunkA`
 *   &b=`@FILE:assets/chunks/variant_b.tpl`
 *   &c=`ChunkC`
 *   &default=`ChunkA`
 *   &docId=`[*id*]`
 *   &userHash=``
 *   &prefix=`{`
 *   &suffix=`}`
 *   &placeholders=`[]`
 * !]
 *
 * Примеры:
 * [!ABVariantTest? &flag=`header_test` &a=`HeaderA` &b=`HeaderB` &c=`HeaderC`!]
 * [!ABVariantTest? &flag=`promo_test` &a=`@FILE:assets/chunks/promo_a.tpl` &b=`@FILE:assets/chunks/promo_b.tpl`!]
 */

use EvolutionCMS\FeatureFlags\Infrastructure\Context\ModxContextMapper;
use FeatureFlags\Core\Application\Service\FeatureFlagService;
use FeatureFlags\Core\Domain\Logger\FlagUsageLoggerInterface;

// ============================================================================
// 1. Чтение и валидация параметров
// ============================================================================

$flagName = trim($params['flag'] ?? '');
$variantA = trim($params['a'] ?? '');
$variantB = trim($params['b'] ?? '');
$variantC = trim($params['c'] ?? '');
$defaultVariant = trim($params['default'] ?? $variantA);

// Плейсхолдеры и их префиксы/суффиксы
$placeholders = is_string($params['placeholders'] ?? '')
    ? json_decode($params['placeholders'], true)
    : ($params['placeholders'] ?? []);
$prefix = trim($params['prefix'] ?? '{');
$suffix = trim($params['suffix'] ?? '}');

// ID документа: из параметра или текущий
$docId = isset($params['docId'])
    ? (int) $params['docId']
    : (evo()->documentIdentifier ?? 0);

// Хеш пользователя: из параметра или вычисляемый
$userHash = trim($params['userHash'] ?? '');
if ($userHash === '') {
    $userHash = md5(
        ($_SERVER['REMOTE_ADDR'] ?? '') .
        ($_SERVER['HTTP_USER_AGENT'] ?? '') .
        ($docId ? '_doc_' . $docId : '')
    );
}

// Если не указан флаг или нет ни одного варианта — выходим
if ($flagName === '' || ($variantA === '' && $variantB === '' && $variantC === '')) {
    return '';
}

// ============================================================================
// 2. Сборка контекста для оценки флага
// ============================================================================

$rawContext = [
    'target_id'    => (string) $docId,
    'document_id'  => (string) $docId,
    'user_hash'    => $userHash,
    'environment'  => evo()->config['site_mode'] ?? 'production',
    'current_date' => date('Y-m-d'),
];

// Добавляем данные документа, если они доступны
if ($docId > 0 && isset(evo()->documentObject)) {
    $doc = evo()->documentObject;
    $rawContext['template_id'] = (string) ($doc['template'] ?? 0);
    $rawContext['category'] = (string) ($doc['category_id'] ?? $doc['parent'] ?? '');
    $rawContext['pagetitle'] = $doc['pagetitle'] ?? '';
}

// Маппим контекст через адаптер модуля
$mapper = new ModxContextMapper($rawContext);
$context = $mapper->build();

// ============================================================================
// 3. Оценка флага и выбор варианта
// ============================================================================

try {
    /** @var FeatureFlagService $service */
    $service = evo()->make(FeatureFlagService::class);

    $variant = $service->getVariant($flagName, $context);

    // Если getVariant() вернул null — используем дефолт
    $selectedVariant = $variant ?? $defaultVariant;
} catch (Throwable $e) {
    // В продакшене логируем ошибку и возвращаем дефолтный вариант
    if (evo()->config['site_mode'] !== 'production') {
        evo()->logEvent(0, 3, "ABVariantTest error: {$e->getMessage()}", 'FeatureFlags');
    }
    $selectedVariant = $defaultVariant;
}

// ============================================================================
// 4. Рендеринг выбранного варианта
// ============================================================================

/**
 * Рендерит чанк или файл с поддержкой @FILE: синтаксиса
 */
function renderVariant(string $source, array $placeholders, string $prefix, string $suffix): string
{
    $modx = evo();

    // Если пустой источник — возвращаем пустую строку
    if ($source === '') {
        return '';
    }

    // Обработка @FILE: references
    if (stripos($source, '@FILE:') === 0) {
        $filePath = trim(substr($source, 6));

        // Проверка на выход за пределы сайта
        if (strpos($filePath, '../') !== false || strpos($filePath, '..\\') !== false) {
            return '<!-- ABVariantTest: Invalid file path -->';
        }

        // Полный путь к файлу
        $fullPath = $modx->config['base_path'] . $filePath;

        if (!file_exists($fullPath)) {
            return $modx->config['site_mode'] !== 'production'
                ? "<!-- ABVariantTest: File not found: $filePath -->"
                : '';
        }

        $content = file_get_contents($fullPath);

        // Парсим плейсхолдеры вручную для файлов
        foreach ($placeholders as $key => $value) {
            $placeholder = $prefix . $key . $suffix;
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    // Обработка чанков через стандартный парсер Evo
    // parseChunk автоматически подгрузит чанк из БД или кэша
    return $modx->parseChunk($source, $placeholders, $prefix, $suffix) ?? '';
}

// Сопоставляем название варианта с источником
$variantSource = match ($selectedVariant) {
    'B' => $variantB,
    'C' => $variantC,
    default => $variantA !== '' ? $variantA : $defaultVariant,
};

// Подготовка плейсхолдеров: объединяем системные и пользовательские
$chunkPlaceholders = array_merge([
    'variant' => $selectedVariant,
    'doc_id' => $docId,
    'user_hash' => $userHash,
], is_array($placeholders) ? $placeholders : []);

// Рендерим и возвращаем результат
return renderVariant($variantSource, $chunkPlaceholders, $prefix, $suffix);
